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
  
  public function CLIunenroll($id)
  {
    // if before drop, remove node and branche
    // if before withdraw, deactivate node
    // else, do not remove.
  }

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
      $lint = exec('which eslint');
      $output = exec("echo {$_POST['content']} | {$lint} --no-eslintrc --parser-options=ecmaVersion:7  --env browser -f json  --stdin");
    }
    
    return $output;
  }
  
  
  public function CLItest()
  {
    $template = new \bloc\View('views/layouts/email.html');
    $template->content = 'views/layouts/forms/recap.html';

    $output = [];

    \models\Message::TRANSACTION('update', 'brendan.metzger@gmail.com', (string)$template->render($output));
    echo "sent an email " . date();
  }
  
  public function CLIcommits()
  {
    $now = time();
    $git = exec('which git');
    
    // parse the daily log file
    // $path = sprintf("%sdata/%s/log/%s", PATH, \models\Data::$SEMESTER, date('m-d-Y', time()));
    // $doc = new \DOMDocument();
    // $doc->loadXML(sprintf("<log>%s</log>", file_get_contents($path)));
    //
    // $updates = [];
    //
    // // get the students who made updates
    // foreach ((new \DOMXpath($doc))->query('//*[@user]') as $node) {
    //   $sid = $node->getAttribute('user');
    //   if (!array_key_exists($sid, $updates)) {
    //     $updates[$sid] = [];
    //   }
    //   $updates[$sid][$node->getAttribute('file')] = $node->getAttribute('hash');
    // }
    
    // get all students and scan server for updates
    stream_context_set_default(['http' => ['method' => 'HEAD']]);
    $query = "@role!='instructor'";
    // $query = "@id='TRSYCP'";
    foreach (\models\data::instance()->query('//')->find("student[{$query}]") as $student) {
      $out = null;
      // iterate projects and get a report on what to grab.
      $student =  new \models\student($student['@id']);
      // look into global.css and global.js files
      $files = [];
      
      $files[] = "{$student['@url']}/src/js/global.js";
      $files[] = "{$student['@url']}/src/css/global.css";
      
      
      // iterate projects
      foreach ($student->projects['list'] as $iterator) {
        $url   = $iterator['project']->baseurl;
        $title = $iterator['project']->title;
        
        $files[] = "{$url}index.html";
        $files[] = "{$url}README.txt";
        $files[] = "{$student['@url']}/src/js/{$title}.js";
        $files[] = "{$student['@url']}/src/css/{$title}.css";
      }
      chdir(sprintf('%sdata/%s/work/', PATH, \models\Data::$SEMESTER));
      
      $messages = [];
      echo exec(sprintf('%s checkout %s', $git, $student['@key']));
      
      foreach ($files as $key => $file) {
        $headers = get_headers($file, 1);
        $last_mod = round(($now - strtotime($headers['Last-Modified'])) / (60 * 60 * 24), 2);
        
        echo "last modified ". $last_mod . "\n";
        
        if ($last_mod < 1) {
          $path = substr($file, strlen("{$student['@url']}/"));
          if (substr($file, -4) == 'html') {
            // use this to validate HTML and gather response
            $handle = curl_init("https://validator.nu/?outline=yes&doc={$file}&out=json&showsource=yes");
            curl_setopt_array($handle, [
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_USERAGENT => 'BrendanBot/1.0',
            ]);

            if ($result  = json_decode(curl_exec($handle))) {
              $content = $result->source->code;
              $report  = [
                'file'     => $path,
                'sloc'     => count(preg_split('/\n/', $content)),
                'analysis' => 'TODO',
                'messages' => [],
              ];
            
              foreach ($result->messages as $message) {
                $report['messages'][] = [
                  'line'  => $result->messages->lastLine,
                  'type'  => $result->messages->type,
                  'text'  => $result->messages->message,
                ];
              }
              $messages[] = $report;
            }
          } else {
            echo "Checking {$path}\n";
            $content = file_get_contents($file, false, stream_context_create(['http' => ['method' => 'GET']]));
            $report = [
              'file' => $path,
              'sloc' => count(preg_split('/\n/', $content)),
              'messages' => [],
            ];
            if (substr($file, -3) == 'css') {
              $cssvalidator = "https://jigsaw.w3.org/css-validator/validator?output=json&warning=0&profile=css3svg&uri=";
              $report['analysis'] = exec("echo '{$content}' | analyze-css -");
              
              if ($result = json_decode(file_get_contents($cssvalidator . $file, false, stream_context_create(['http' => ['method' => 'GET']])))) {
                if ($result->cssvalidation->result->errorcount > 0) {
                  foreach ($result->cssvalidation->errors as $error) {
                    $report['messages'][] = [
                      'line' => $error->line,
                      'type' => $error->type,
                      'text' => $error->message,
                    ];
                  }
                
                  $messages[] = $report;
                }
              }
               
              //TODO analyze-css !
            } else if (substr($file, -2) == 'js') {
              // TODO sloc
              // TODO eslint
            }
            
            
          }
          // update the file with the new content
          file_put_contents($path, $content);
        } else {
          echo "Not updating {$path}\n";
        }
        usleep(20000);
      }
      print_r($messages);
      echo exec(sprintf('%s commit --all -m %s', $git, '"make a better message"'), $out);
      print_r($out);
    }

    echo exec(sprintf('%s checkout master', $git));
    
  }
}
