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

    static public function BUILD(Student $student, $file = null)
    {
      $zip   = new \ZipArchive;
      $dom   = new \bloc\DOM\Document('data/student-site');
      $xpath = new \DomXpath($dom);

      $parse = function($context) use (&$parse, $xpath, $dom) {
        foreach ($xpath->query('./file', $context) as $file) {
          if ($file->hasAttribute('pattern')) {
            $pattern = strtolower(str_replace(' ', '-', sprintf($file->getAttribute('pattern'), $context->parentNode->getAttribute('title'))));
            $file->setAttribute('name', $pattern);
          }
        }

        foreach ($xpath->query('./directory', $context) as $directory) {
          $name = $directory->getAttribute('name');
          if ($directory->hasAttribute('clone')) {
            $replacements = $xpath->query($directory->getAttribute('clone'));
            foreach ($replacements as $replacement) {
              $cloned = $directory->appendChild($replacement->cloneNode(!$replacement->hasAttribute('clone')));
              foreach ($xpath->query('./file[@clone="ignore"]', $cloned) as $ignore) {
                $cloned->removeChild($ignore);
              }
            }
            $parent = $directory->parentNode;
            $directory = $parent->removeChild($directory);
            $pattern = $directory->getAttribute('pattern');
            foreach (explode('|', $name) as $idx) {
              $newnode = $parent->appendChild($directory->cloneNode(true));
              $newnode->setAttribute('name', strtolower($idx));
              $newnode->setAttribute('title', sprintf($pattern, $idx));
              $parse($newnode);
            }
          } else if ($directory->childNodes->length > 0) {
            $parse($directory);
          }
        }
      };

      call_user_func($parse, $dom->documentElement, '', './');
      $filepath = 'views/student/site';
      $cache = [];
      $zip->open($file, \ZIPARCHIVE::OVERWRITE);

      $zipper = function($context, $path) use(&$zipper, $zip, $filepath, $student, &$cache) {
        $path .= '/';

        $title = $context->getAttribute('title') ?: '';
        foreach ($context->childNodes as $level) {
          $type = $level->nodeName;
          $name = $level->getAttribute('name');
          if ($type == 'file') {
            $ext = substr($name, strpos($name, '.'));
            $base = substr($name, 0, strpos($name, '.'));
            if (! array_key_exists($name, $cache)) {
              if (file_exists(PATH.$filepath . $path . $name)) {
                $cache[$name] = $filepath . $path . $name;
              } else if (file_exists(PATH.$filepath . $level->getAttribute('template'))) {
                $cache[$name] = $filepath . $level->getAttribute('template');
              }
            }

            if ($ext == '.html' || $ext == '.xml') {
              $view = new \bloc\view($cache[$name]);
              if ($context->hasAttribute('template')) {
                $view->template = $filepath.$context->getAttribute('template');
              }

              $text = (string)$view->render([
				'id' => $name,
                'student' => $student,
                'title' => $title,
                'datapath' => substr($path, 1, -6),
                'embeds' => strtolower(str_replace(' ', '-', $title)),
                'domain' => DOMAIN,
              ]);

              if ($ext != '.xml') {
                $text = substr($text, strlen('<?xml version="1.0"?>'));
              }
              $zip->addFromString(substr($path.$name, 1), $text);
            } else {
              $zip->addFile(PATH.$cache[$name], substr($path.$name,1));
            }
          } else {
            $zip->addEmptyDir($path.'/');
            $zipper($level, $path.$name);
          }
        }
      };

      $zipper($dom->documentElement, '', '');
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
