<?php

namespace models;

/**
  * Outline
  *
  */

  class Outline extends \bloc\Model
  {
    use traits\resolver, traits\persist;

    const XPATH = '/course/classes/';

    static public $fixture = [
      'outline' => [
        '@' => ['title' => ''],
        'assignment' => [],
      ]
    ];
    
    static public function TEMPLATE(Student $student, $file = null)
    {
      $zip   = new \ZipArchive;
      $data = [
        'empty'   => null,
        'student' => $student,
        'domain' => DOMAIN,
      ];
      $trimmable = strlen("<?xml version=\"1.0\"?>\n");
      $template = 'data/template/site/';
      $zip->open($file, \ZIPARCHIVE::OVERWRITE);

      // make a folder for class work and studies
      foreach ($student->section->schedule as $date) {
        if ($date['status'] == 'holiday') continue;
        $format = $date['object']->format('m-d-y');
        $dir = '/studies/'.$format;
        $zip->addEmptyDir($dir);

        $view = new \bloc\view($template.'index.html');
        $view->footer = "{$template}/templates/up.html";     
        $view->template = "{$template}templates/notes.html";
        
        $zip->addFromString($dir.'/index.html', substr($view->render(array_merge($data, [
          'title' => 'Notes ' . $date['datetime'],
          'resource' => $format,
        ])), $trimmable));
        
        $zip->addFromString("{$dir}/{$format}.css", "/* {$format} Stylesheet */");
        $zip->addFromString("{$dir}/{$format}.js", "// {$format} JS */"); 
      }      
      
      // Add a file for each project
      foreach ($student->projects->list as $project) {
        $title = $project['project']->title;
        $view = new \bloc\view($template.'index.html');
        $view->footer = "{$template}/templates/copyright.html";
        if ($title != 'final') {
          $view->template = "{$template}templates/project.html";
          $dir = "/{$title}/";
          $readme = "# Notes for {$title} project\n\nLook at the dates below that govern the timeline for this project: use them to project-manage goals, technical difficulties, and workflow.";
          $readme .= "\n\n## " . implode("\n\n## ", array_map(function($date) {
              return $date->format('l F jS, Y');
            }, iterator_to_array(new \DatePeriod($project['schedule']['object'], new \DateInterval('P1D') ,$project['due']['object']))));
        } else {
          $view->template = "{$template}templates/abstract.html";
          $dir = "/";
          $readme = "# This is your main readme file\n\nAnd this is where you tell me about your everything that happened with this class, this project, and this semester. [Markdown format](https://en.wikipedia.org/wiki/Markdown) is appreciated";
        }

        $zip->addFromString("{$dir}index.html", substr($view->render(array_merge($data, [
          'title'    => $title,
          'resource' => $title,
        ])), $trimmable));
        
        $zip->addFromString("/{$dir}/README.txt", $readme);
        $zip->addFromString("/src/css/{$title}.css", "/*\n{$title} stylesheet TODO:\n - [ ] copy list from course outline*/");
        $zip->addFromString("/src/js/{$title}.js", "/*\n{$title} javascript TODO:\n - [ ] copy list from course outline*/");
      }
      
      // add media
      foreach (glob(PATH.'data/template/site/media/*.*') as $file) {
        $zip->addFile($file, '/assets/'.basename($file));
      }
      
      // add global src files
      $zip->addFile(PATH.'data/template/src/global.css', '/src/css/global.css');
      $zip->addFile(PATH.'data/template/src/global.js', '/src/js/global.js');
      
      $zip->close();
      return $file;
    }
    

    public function getAssignments(\DOMElement $context)
    {
      return $context->find('assignment')->map(function($item) {
        return ['assignment' => new Criterion($item['@ref'])];
      });
    }

    public function getdate(\DOMElement $context)
    {
      return (new \DateTime($context['@datetime']))->format('l, F jS, Y');
    }
}
