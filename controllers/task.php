<?php
namespace controllers;

use \bloc\application;
use \bloc\DOM\Document;

/**
 * Third Coast International Audio Festival Defaults
 */

class Task extends \bloc\controller
{
  const SEMESTER = 'data/36-1420-01-FA15';
  public function __construct($request)
  {
  }

  public function authenticate()
  {
    return true;
  }

  private function save($doc)
  {
    if ($doc->validate()) {
      $doc->save();
    } else {
      echo "ERROR \n";
      print_r($doc->errors());
      echo "\n";
    }
  }

  public function CLIindex()
  {
    // show a list of methods.
    $reflection_class = new \ReflectionClass($this);

    $instance_class_name = get_class($this);
    $parent_class_name = $reflection_class->getParentClass()->name;
    $methods = ['instance' => [], 'parent' => []];
    foreach ($reflection_class->getMethods() as $method) {
      if (substr($method->name, 0, 3) == 'CLI') {
        $name = $method->getDeclaringClass()->name;
        if ($instance_class_name == $name) {
          $methods['instance'][] = substr($method->name, 3) . "\n";
        }
        if ($parent_class_name == $name) {
          $methods['parent'][] = substr($method->name, 3) . "\n";
        }
      }
    }

    echo "Available Methods in {$instance_class_name}\n";
    print_r($methods);
  }

  public function CLItemplate()
  {
    \models\data::$DB = 'SP16';
    // should return a zip file
    $student = new \models\student(\models\data::ID('YNUZ'));
    $outline = \models\outline::BUILD($student, $student->course . '.zip');
  }

  public function CLIout()
  {
    $doc  = new Document("data/SP16");
    $doc->xinclude();
    $this->save($doc);
  }

  public function CLIimport($semester, $course, $section = 01)
  {
    $path  = "data/classlists/{$semester}-{$course}-{$section}";

    $doc  = new Document("data/{$semester}");
    $xml  = new \DomXpath($doc);
    $section = $xml->query("//courses/section[@id='{$section}' and @course='{$course}']");
    if ($section->length > 0) {
      $section = $section->item(0);
      $imports = $this->parseStudentFile(new \DomXpath(new Document($path)));
      $translation = ['0123456789', 'QRSTUVWXYZ'];

      foreach ($imports as $import) {
        $student = $section->appendChild($doc->createElement('student'));
        $student->setAttribute('name', $import['name']);
        $student->setAttribute('email', $import['email']);
        $key  = substr($import['email'], 0, strpos($import['email'], '@'));
        $student->setAttribute('url', "http://iam.colum.edu/students/{$key}/{$course}/");
        $student->setAttribute('id', \models\Student::BLEAR($import['id']));
      }
    }
    $this->save($doc);
  }

  private function parseStudentFile($xml)
  {
    $headers = [];
    foreach ($xml->query("//tr[@class='glbfieldname']/td") as $index => $header) {
      $key = strtolower(trim($header->nodeValue));
      $headers[$key] = $index;
    }

    $callbacks = [
      'email' => function ($node) {
        return substr($node->firstChild->getAttribute('href'), 7);
      },
      'name' => function ($node) {
        $split = preg_replace_callback('/(.*),\s+(.*)/', function($matches) {
          return $matches[2] . ' ' . $matches[1];
        }, $node->firstChild->nodeValue);
        return $split;
      },
      'id' => function ($node) {
        return (int)trim($node->nodeValue);
      },
      'cl' => function ($node) {
        return $node->nodeValue;
      },
      'grad' => function ($node) {
        return $node->nodeValue ?: 'N';
      }
    ];
    $students = [];
    foreach ($xml->query("//tr[@class='glbdatadark']") as $row) {
      $fields = $row->childNodes;
      $keys   = array_keys($callbacks);
      $students[] = array_combine($keys, array_map(function ($key, $callback) use ($fields, $headers) {
        return trim(call_user_func($callback, $fields->item($headers[$key])));
      }, $keys, $callbacks));
    }
    return $students;
  }


  public function CLIbuildWeeks()
  {
    $doc  = new \bloc\DOM\Document(self::SEMESTER);
    $xml  = new \DomXpath($doc);

    $classes = $xml->query('/course/classes')->item(0);
    echo "here";
    for ($i=1; $i < 16; $i++) {
      $week = $classes->appendChild($doc->createElement('week'));
      $week->setAttribute('index',  $i);
      echo "\nWeek {$i} Class title: ";
      $week->setAttribute('title', trim(fgets(STDIN)));
    }

    $this->save($doc);
  }

  public function CLIassignment()
  {
    $doc  = new \bloc\DOM\Document(self::SEMESTER);
    $xml  = new \DomXpath($doc);

    $assignment = $xml->query('/course/assignments')->item(0)->appendChild($doc->createElement('assignment'));
    $weeks      = $xml->query('/course/classes/outline');
    // title, points, id

    echo "\nEnter Assignment Title: ";
    $title = trim(fgets(STDIN));
    $assignment->setAttribute('title', $title);

    $id = preg_replace(['/\s+/', '/[^a-z]/i'], ['-', ''], $title);
    $assignment->setAttribute('id', $id);

    echo "\nEnter number of points: ";
    $points = trim(fgets(STDIN));
    $assignment->setAttribute('points', (int)$points);

    echo "\nWeek number: ";

    $week = ((int)trim(fgets(STDIN)) - 1);
    if ($week >= 0 && $week < 15) {
      $ref = $weeks->item($week)->appendChild($doc->createElement('assignment'));
      $ref->setAttribute('ref', $id);
    }

    echo "\nAssignment Details: ";
    $details = trim(fgets(STDIN));

    if (!empty($details)) {
      $assignment->setNodeValue($details);
    }

    $this->save($doc);
  }
}
