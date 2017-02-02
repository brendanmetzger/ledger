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
      /*
        TODO Structure of template should come entirely from a model/fixture
      */
      \bloc\view::removeRenderers();
      \bloc\view::$edit = false;
      $zip  = new \ZipArchive;
      $data = [
        'empty'   => null,
        'code'    => base64_encode($student['@name']),
        'id'      => $student['@id'],
        'student' => $student,
        'domain'  => DOMAIN,
        'cdn'     => getenv('MODE') === 'local' ? DOMAIN : "{$_SERVER['REQUEST_SCHEME']}://cdn.thirty.cc",
      ];
      $trimmable = strlen("<?xml version=\"1.0\"?>\n");
      $template = 'data/template';
      $zip->open($file, \ZIPARCHIVE::OVERWRITE);
      
      $snippet = "Rename this file and make into something useful ;)\n\n";
      // make a folder for class work and studies
      foreach ($student->section->schedule as $date) {
        if ($date['status'] == 'holiday') continue;
        $format = $date['object']->format('m-d-Y');
        $snippet .= "<li><a href=\"./{$format}/index.html\">{$date['date']}</a></li>\n";
        $dir = '/studies/'.$format;
        // $zip->addEmptyDir($dir);
        
        $view = new \bloc\view("{$template}/layout.html");
        $view->body = "{$template}/notes.html";

        $zip->addFromString("{$dir}/index.html", substr($view->render(array_merge($data, [
          'title' => 'Notes ' . $date['datetime'],
          'resource' => $format,
        ])), $trimmable));
        
        $zip->addFromString("{$dir}/{$format}.css", "/* {$format} Stylesheet */");
        $zip->addFromString("{$dir}/{$format}.js", "// {$format} JS */"); 
      }
      
      $zip->addFromString("/studies/TODO.txt", $snippet);
      
      $quotes = new \bloc\DOM\Document('data/quotes');
      $xpath  = new \DomXpath($quotes);
      // Add a file for each project
      foreach ($student->projects->list as $project) {
        $title = $project['project']->title;
        $view = new \bloc\view("{$template}/layout.html");
        if ($title != 'final') {
          $view->body = "{$template}/project.html";

          $dir = "/{$title}/";
          $readme = "# Notes for {$title} project\n\n## TODO\n{$project['project']['criterion']}\n\nLook at the dates below: I urge you to use them to create an outline and journal your agenda, project-manage goals, note technical difficulties and jot ideas.";
          $readme .= "\n\n## Log\n" . implode("\n", array_map(function($date) {
              return $date->format('l F jS, Y');
            }, iterator_to_array(new \DatePeriod($project['schedule']['object'], new \DateInterval('P1D') ,$project['due']['object']))));
        } else {
          $view->body = "{$template}/abstract.html";
          $dir = "/";
          $readme = "# This is your main readme file\n\nAnd this is where you tell me about your everything that happened with this class, this project, and this semester. [Markdown format](https://en.wikipedia.org/wiki/Markdown) is appreciated";
        }

        $zip->addFromString("{$dir}index.html", substr($view->render(array_merge($data, [
          'description' => $project['project']['criterion'],
          'quote'       => $xpath->query("//quote[@for='{$title}']")->item(0),
          'title'    => $title,
          'resource' => $title,
        ])), $trimmable));
        
        $zip->addFromString("/{$dir}/README.txt", $readme);
        $zip->addFromString("/src/css/{$title}.css", "/*\n{$title} StyleSheet TODO:\n - [ ] copy list from course outline\n*/");
        $zip->addFromString("/src/js/{$title}.js", "/*\n{$title} JavaScript TODO:\n - [ ] copy list from course outline\n*/");
      }

      // add media
      foreach (glob(PATH.'data/template/media/*.*') as $file) {
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
