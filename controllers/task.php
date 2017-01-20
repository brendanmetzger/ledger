<?php
namespace controllers;

use \bloc\application;
use \bloc\DOM\Document;

/**
 * Third Coast International Audio Festival Defaults
 */

class Task extends \bloc\controller
{
  
  use traits\config;
  const SEMESTER = 'data/36-1420-01-FA15';
  
  public function GETsource($url, $encode = false)
  {
    $url = base64_decode($url);
    $content = file_get_contents($url);
    return  $encode ? htmlentities($content) : $content;
  }

  public function GETauth($api = 'TBD')
  {
    echo "<app>{$api} - {$_GET['code']}</app>";
    // https://api.instagram.com/v1/users/self/?access_token=ACCESS-TOKEN
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

  public function CLIimport($semester, $course, $section = '01')
  {
    $path  =  "data/classlists/{$semester}-{$course}-{$section}";
    $imports = $this->parseStudentFile(new \DomXpath(new Document($path)));
    foreach ($imports as $import) {
      $this->CLIenroll($semester, $course, $section, $import['id'], $import['name'], $import['email'], $import['cl'], $import['major']);
    }
  }

  public function CLIenroll($semester, $course, $section, $id = null, $name = null, $email = null, $year = null, $major = null)
  {
    $doc     = new Document("data/{$semester}");
    $section = (new \DomXpath($doc))->query("//courses/section[@id='{$section}' and @course='{$course}']");
    if ($section->length < 1) {
      throw new \RuntimeException("No section {$section} for {$course} in {$semester}", 1);
    }

    $student = $section->item(0)->appendChild($doc->createElement('student'));

    if ($name === null) {
      echo "\nName: ";
      $name = trim(fgets(STDIN));
    }

    if ($id === null) {
      echo "\nOasis ID: ";
      $id = trim(fgets(STDIN));
    }

    if ($email === null) {
      echo "\nEmail: ";
      $email = trim(fgets(STDIN));
    }


    if ($year === null) {
      echo "\nYear: ";
      $year = trim(fgets(STDIN));
    }

    if ($major === null) {
      echo "\nMajor: ";
      $major = trim(fgets(STDIN));
    }
    $student->setAttribute('name', $name);
    $student->setAttribute('email', $email);
    $key  = substr($email, 0, strpos($email, '@'));
    $student->setAttribute('url', "http://iam.colum.edu/students/{$key}/{$course}");
    $student->setAttribute('id', \models\Student::BLEAR($id));
    $student->setAttribute('year', $year);
    $student->setAttribute('major', $major);
    echo $student->write() . "\nCreate Account (Y/n): ";
    if (strtoupper(trim(fgets(STDIN))) === 'Y') {
      return $this->save($doc);
    }
  }

  public function CLIlines()
  {
    $doc     = new Document("data/FA16");
    $notes = (new \DomXpath($doc))->query("//student/quiz");

    foreach ($notes as $note) {
      if (strpos($note->nodeValue, ' ') !== false) {
        echo "FIX: " . substr($note->nodeValue, 0, 25) . "\n";
        $note->nodeValue = base64_encode($note->nodeValue);
      }
      
    }

    $this->save($doc);
  }

  public function CLImake(string $semester,  $path = 'data/student/')
  {
    echo "Will create a directory for each student in {$path}";

    $doc = new Document("data/{$semester}");
    $students = (new \DomXpath($doc))->query("//student[@id]");

    foreach ($students as $student) {

      $student_directory_path = PATH.$path.$student->getAttribute('id').'.xml';
      echo "Creating file: {$student_directory_path}\n";
      file_put_contents($student_directory_path, '<records/>');
    }


    // gather list of students
    // make a path
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
      },
      'major' => function($node) {
        return $node->nodeValue;
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
  /**
   * README file as full-page documentation
   *
   * @param string $readme the name of the file
   * @return string
   */
  public function GETdocumentation($readme)
  {
    // parse comments
    // \/\*([\s\S]*?)\*\/|([^\\:]|^)\/\/.*$
  }
  
  public function POSTvalidate($request, $id, $type, $url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, base64_decode($url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    // log transaction, time, user, file, hash
    $hash = sha1($_POST['content']);
    $time = time();
    $path = sprintf("%sdata/%s/log/%s", PATH, \models\Data::$SEMESTER, date('m-d-Y', time()));
    $out = "<validate user=\"{$id}\" time=\"{$time}\" hash=\"{$hash}\"/>\n";
    if ($type == 'css') {
      # code...
    } else if ($type == 'html') {
      
    }
    file_put_contents($path, $out, FILE_APPEND);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return $output;
  }
  
  public function CLIcommits()
  {
    echo 'no';
    /*
    - parse the daily log file
    - for each student with activity
    
    */
  }

}
