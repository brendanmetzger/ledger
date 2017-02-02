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

  public function authenticate($user = null)
  {
    return true;
  }

  private function save($doc)
  {
    if ($doc->validate()) {
      return $doc->save();
    } else {
      echo "ERROR \n";
      print_r($doc->errors());
      echo "\n";
      return false;
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
    $path  =  "data/{$semester}/{$course}-{$section}";
    $imports = $this->parseStudentFile(new \DomXpath(new Document($path)));
    foreach ($imports as $import) {
      $this->CLIenroll($semester, $course, $section, $import['id'], $import['name'], $import['email'], $import['cl'], $import['major']);
    }
  }
  
  /*
    TODO Rather than the manual process, it would be nice to have a command.
  */
  public function CLIunenroll($id)
  {
    // if before drop, remove node and branch
    // if before withdraw, deactivate node
    // else, do not remove.
  }
  
  public function CLItoken($subject = false)
  {
    $token = new \bloc\types\token('http://nonsesnse');
    
    if ($subject) {
      
      echo "{$subject}\n\n" . $token->gen($subject, 'some secret') . "\n\n";
    } else {
      $key = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJITUFDU0hBMjU2In0%3D.eyJpYXQiOjE0ODU5ODIwOTksImV4cCI6MTQ4ODU3NDA5OSwic2NvcGVzIjpbXSwiaXNzIjoiaHR0cDpcL1wvbm9uc2VzbnNlIiwic3ViIjoidGVzdGVyYW9vIn0%3D.1f95775e149e7feded2aa86eb0c86de94962956324ef0d878ac055451f6d78c8';
      // $out = $token->validate($key, 'some secret');
      // print_r($out);
      $id = $token->validate($key, 'some secret')->sub;
      echo "\n\n {$id} \n\n";
    }
  }

  /*
    TODO Add most of this functionality to the student model, perhaps a static method
  */
  public function CLIenroll($semester, $course, $section, $id = null, $name = null, $email = null, $year = null, $major = null)
  {
    $doc     = new Document("data/{$semester}/records");
    $path    = sprintf("%sdata/%s/work/", PATH, $semester);
    $git     = exec('which git');
    $section = (new \DomXpath($doc))->query("//courses/section[@id='{$section}' and @course='{$course}']");
    if ($section->length < 1) {
      throw new \RuntimeException("No section {$section} for {$course} in {$semester}", 1);
    }

    $student = $section->item(0)->appendChild($doc->createElement('student'));

    if ($name === null) {
      echo "\nName: ";
      $name = trim(fgets(STDIN));
    } else {
      echo "Student is '{$name}'\n";
      echo "Enter if ok, or new name to adjust:";
      $adjustment = trim(fgets(STDIN));
      if (!empty($adjustment)) {
        $name = $adjustment;
      }
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
    $key  = strtolower(substr($email, 0, strpos($email, '@')));
    $student->setAttribute('key', $key);
    $student->setAttribute('url', "http://iam.colum.edu/students/{$key}/{$course}");
    $student->setAttribute('id', \models\Student::BLEAR($id));
    $student->setAttribute('year', $year);
    $student->setAttribute('role', $major);
    echo $student->write() . "\nCreate Account (Y/n): ";
    if (strtoupper(trim(fgets(STDIN))) === 'Y') {
      $saved = $this->save($doc);
      
      if ($saved) {
        $object = new \models\student($student);
        // create and checkout branch in data/SEM/work/
        
        chdir($path);
        
        $cmds = [];
        
        $cmds[] = sprintf('%s checkout -b %s', $git, $key);
        $cmds[] = 'mkdir -p src/js';
        $cmds[] = 'mkdir -p src/css';
        $cmds[] = 'touch src/js/global.js';
        $cmds[] = 'touch src/css/global.css';
        
        
        // iterate projects
        foreach ($object->projects['list'] as $iterator) {
          $dir   = trim($iterator['project']['criterion']['@path'], '/');
          $title = $iterator['project']['criterion']['@title'];

          if (! empty($dir)) {
            $cmds[] = sprintf('mkdir %s', $dir);
            $dir .= '/';
          }
          
          $cmds[] = sprintf('touch %sindex.html', $dir);
          $cmds[] = sprintf('touch %sREADME.txt', $dir);
          $cmds[] = sprintf('touch src/js/%s.js', $title);
          $cmds[] = sprintf('touch src/css/%s.css', $title);
        }
        $cmds[] = sprintf('%s add --all', $git);
        $cmds[] = sprintf('%s commit -m %s', $git, "'initiated branch'");
        $cmds[] = sprintf('%s checkout master', $git);
        // run first commit
        foreach ($cmds as $cmd) {
          echo "{$cmd}\n";
          exec($cmd, $out);
          print_r($out);
          usleep(1000);
        }
      }
      return true;
    }
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
  
  public function POSTvalidate($request, $id, $type, $url, $file)
  {
    // log transaction, time, user, file, hash
    $hash = sha1($_POST['content']);
    $time = time();
    $file = base64_decode($file);
    $path = sprintf("%sdata/%s/log/%s", PATH, \models\Data::$SEMESTER, date('m-d-Y', time()));
    $out = "<validate user=\"{$id}\" time=\"{$time}\" file=\"{$file}\" hash=\"{$hash}\"/>\n";
    file_put_contents("/tmp/{$hash}", gzcompress($_POST['content']));
    file_put_contents($path, $out, FILE_APPEND);

    if ($type == 'css' || $type == 'html') {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, base64_decode($url));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
      curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
      $output = curl_exec($ch);
      $info = curl_getinfo($ch);
      curl_close($ch);
      
    } else if ($type == 'js') {
      $lint   = exec('which eslint');
      $output = exec("echo {$_POST['content']} | {$lint} --no-eslintrc --parser-options=ecmaVersion:7  --env browser -f json  --stdin");
    }
    
    return $output;
  }
  
  
  public function CLIcommits()
  {
    $sem  = \models\Data::$SEMESTER;
    $git  = new \models\Source($sem);
    $date = (new \DateTime())->modify('-1 day')->setTime(12, 0)->format(DATE_ISO8601);
    
    chdir(sprintf('%sdata/%s/work/', PATH, $sem));
    
    foreach (\models\data::instance()->query('//')->find("student[@role!='instructor']") as $node) {
      $student = new \models\student($node['@id']);
      
      // get the current week
      $week    = \models\calendar::INDEX($student->section->schedule);
      $start   = $student->section->schedule[0]['object']->format(DATE_ISO8601);
      
      echo "--- Checking {$student['@name']} ---\n";
      $git->checkout($student['@key']);

      foreach ($student->projects['list'] as $iterator) {
        // Track the two * auxillary files
        $auxillary = [];
        $project   = $iterator['project'];
        
        // then check all project files
        foreach ($project['file'] as $file) {
          // Gather the report. This is complicated because some files can be used in
          // in multiple projects, so to optimize redundant checks, cache them
          if ($file['@aux'] == '*') {
            $report = $auxillary[$file['@path']] ?? $auxillary[$file['@path']] = new \models\Report($student->domain, $file['@path']);
          } else {
            $report = new \models\Report($student->domain, $file['@path']);
          }
          
          echo "-- CHECKING {$file['@path']} for {$student['@name']}\n";
          
          $commits = count($git->log($file['@path'], "--after='{$start}'")) . "\n\n";
          $file->setAttribute('age', $report->getLastModified(86400));
          
          if ((int)$file['@age'] > 1) continue;
          
          echo "---- UPDATING... ";
          
          $file->setAttribute('errors', $report->getErrors());
          $file->setAttribute('sloc', $report->getSLOC());
          $file->setAttribute('length', $report->getSize());
          $file->setAttribute('hash', $report->getHash());
          $file->setAttribute('report', $report);
          $file->setAttribute('commits', $commits + ($report->getHash() == $file['@hash'] ? 0 : 1));
          
          // save file
          echo (! $report->save() ?  'ERROR' : 'success') . " on save\n";

          // pause for a 1/5 second so we don't upset anyone
          usleep(200000);
        }
                
        // save project, print errors if any problems
        if (! $project->save()) print_r($project->errors);

      }
      
      $commit = $git->commit($git->diff('--shortstat'), "--date=\"{$date}\"");
      print_r($commit);
    }
    print_r($git->push('master', '--all'));
  }
  
  public function CLIbackup($semester, $message = 'Automated Push')
  {
    // checkout current semester
    $git = new \models\source($semester, '%sdata/%s');
    $git->commit($message);
    $git->push('master', 'origin');
  }
  
  public function CLIpull($chmod)
  {
    $semester = \models\Data::$SEMESTER;
    $git = new \models\Source($semester);
    
    foreach (\models\data::instance()->query('//')->find("student[@role!='instructor']") as $student) {
      echo $git->checkout($student['@key']) . "\n";
      echo $git->execute('pull') . "\n";
    }
  }
  
}
