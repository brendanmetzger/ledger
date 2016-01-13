<?php
namespace controllers;

use \bloc\application;

/**
 * Third Coast International Audio Festival Defaults
 */

class Task extends \bloc\controller
{
  const SEMESTER = 'data/36-1420-01-FA15';
  public function __construct($request)
  {
  }

  private function save($doc)
  {
    if ($doc->validate()) {
      $doc->save(PATH . self::SEMESTER . '.xml');
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

  public function CLItemplate($what)
  {
    if ($what === 'course') {
      echo "getting started";
      $archive_name   = PATH."data/views.zip"; // name of zip file
      $archive_folder = PATH . 'views'; // the folder which you archivate

      $zip = new \ZipArchive;

      if ($zip->open($archive_name, ZipArchive::CREATE) === TRUE) {
        $dir = preg_replace('/[\/]{2,}/', '/', $archive_folder."/");

        $dirs = array($dir);
        while (count($dirs)) {
          $dir = current($dirs);
          $zip -> addEmptyDir($dir);

          $dh = opendir($dir);
          while($file = readdir($dh)) {
            if ($file != '.' && $file != '..') {
              if (is_file($file)) {
                $zip -> addFile($dir.$file, $dir.$file);
              } else if (is_dir($file)) {
                $dirs[] = $dir.$file."/";
              }
            }
          }
          closedir($dh);
          array_shift($dirs);
        }
        $zip -> close();
        echo 'Archiving is sucessful!';
      }
      else {
        echo 'Error, can\'t create a zip file!';
      }
    }
  }

  public function CLItranslate()
  {
    $doc  = new \bloc\DOM\Document(self::SEMESTER);
    $xml  = new \DomXpath($doc);

    $students = $xml->query('/course/members/student');
    $table = \models\data::$translation;

    foreach ($students as $student) {
      $id = strtoupper(strtr(dechex($student['@id']), $table['numbers'], $table['letters']));
      $student->setAttribute('id', $id);
    }

    $this->save($doc);
  }

  public function CLIstudent($name = false)
  {

    $doc  = new \bloc\DOM\Document(self::SEMESTER);
    $xml  = new \DomXpath($doc);

    $table = \models\data::$translation;

    $student = $xml->query('/course/members')->item(0)->appendChild($doc->createElement('student'));

    echo "\nEnter Oasis ID: ";
    $id = strtoupper(strtr(dechex(trim(fgets(STDIN))), $table['numbers'], $table['letters']));
    $student->setAttribute('id', $id);

    if (!$name) {
      echo "\nEnter Student Name: ";
      $name = trim(fgets(STDIN));
    }
    $student->setAttribute('name', $name);

    echo "\nEnter email address: ";
    $email = trim(fgets(STDIN));
    $student->setAttribute('email', $email);

    echo "\nEnter class site: ";
    $url = trim(fgets(STDIN));
    $student->setAttribute('url', $url);

    $this->save($doc);
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
