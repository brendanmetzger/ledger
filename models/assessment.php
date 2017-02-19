<?php
namespace models;

/**
 * Event
 */
 class Assessment
 {
    static public $fixture = [
      'calendar' => [
        'event' => [],
      ]
    ];

    static public $weight = [
      'discourse' => 30,
      'practice'  => 20,
      'quiz'      => 0,
      'project'   => 50,
    ];

    public static $rubric = [
      'A'  => 0.93,
      'A-' => 0.9,
      'B+' => 0.87,
      'B'  => 0.83,
      'B-' => 0.8,
      'C+' => 0.77,
      'C'  => 0.73,
      'C-' => 0.70,
      'D'  => 0.60,
      'F'  => 0,
    ];


    private static $storage;
    private $student, $section;

    public function __construct(\bloc\model $model)
    {
      $this->student = $model;
    }

    private function collective(Section $section, $c, $raw = 'score')
    {
      if (! $storage = self::$storage) {
        $storage = self::$storage = new \bloc\Maybe([]);
      }

      $key = $c['@type'].$c['@index'].$section['@id'].$section['@course'];

      return $storage($key)->get(function ($key) use($section, $c, $raw) {
        $scores = [];
        foreach ($section->students as $item) {
          $context = $item['student']->context->getElement($c['@type'], $c['@index']);
          $score = Data::FACTORY($c['@type'], $context, null, [new \models\Criterion($c)])->{$raw};
          if ($score > 0) {
            $scores[] = $score;
          }
        }


        sort($scores);
        $total = count($scores);
        $out = [
          'max'  => 0,
          'min'  => 0,
          'med'  => 0,
          'sum'  => 0,
          'sd'   => 0,
          'mean' => 0,
          'wmean'=> 0,
          'var'  => 0,
          'z'    => 0,
        ];

        if ($total > 1) {
          $out['min']  = round($scores[0], 2);
          $out['max']  = round($scores[$total-1], 2);
          $out['sum']  = array_sum($scores);
          $out['mean'] = round($out['sum'] / $total, 2);
          $out['wmean'] = 1 - ($out['max'] - $out['mean']);
          $out['var']  = array_sum(array_map(function ($item) use($out){
            return pow($item - $out['mean'], 2);
          }, $scores)) / $total;

          $out['sd'] = round(sqrt($out['var']), 2);

        }

        return $out;
      });
    }

    static public function GRADEBOOK($section)
    {

      $students = $section->students;
      $book = [
        'practice' => [],
        'discourse' => [],
        'project'   => [],
        'quiz'   => [],
      ];


      foreach ($book as $key => &$record) {

        foreach ($section->assignments($key) as $index => $item) {
          
          $records = [];
          
          foreach ($students as $student) {

            $records[] = [
              'assessment' => $student['student']->{$key}->list[$item['@index']],
              'student'    => $student['student'],
            ];
            
          }
          
          $record[] = [
            'criterion' => $item,
            'due' => $student['student']->section->schedule[$item['@assigned'] ?? $index],
            'title' => $item['@title'] ?? $item['@index'],
            'records' => $records,
          ];
        }
        

      }
      return new \bloc\types\Dictionary($book);
    }

    static public function LETTER($score, $multiplier = 1)
    {
      $score = round($score);
      foreach (self::$rubric as $letter => $threshold) {
        if ($score >= ($threshold * $multiplier)) {
          return $letter;
        }
      }
      return '-';
    }

    static public function LINKS($assessment)
    {
      $doc = $assessment->markup;

      $xpath = new \DOMXpath($doc);

      $report = $assessment->validate;
      // FIXME: report now contains warnings.
      $number_of_errors = count($report->messages);
      $files = [
        [
          'name'    => 'index.html',
          'content' => base64_decode($assessment->plain->getAttribute('content')),
          'type'    => 'lang-html',
          'url'     => $assessment->url,
          'report'  => ['sloc'    => $assessment->plain->getAttribute('sloc'), 'count' => count($report->messages), 'errors' => (new \bloc\types\Dictionary($report->messages))->map(function ($item) {
            return ['line' => $item->lastLine ?? 1, 'message' => $item->message, 'type' => $item->type];
          })],

        ],
        [
          'name'    => 'README',
          'content' => null,
          'type'    => 'plain-text',
          'url'     => base64_encode($assessment->url . '/readme.txt'),
        ]
      ];


      foreach ($xpath->query("//script[@src and not(contains(@src, 'http'))]") as $file) {
        $src = $file->getAttribute('src');
        $files[] = [
          'url'     => base64_encode($assessment->url . '/' .$src),
          'name'    => substr($src, strrpos($src, '/') + 1),
          'content' => null,
          'type'    => 'lang-js',
        ];
      }

      foreach ($xpath->query("//link[not(contains(@href, 'http')) and contains(@href, '.css')]") as $file) {

        $src = $file->getAttribute('href');
        $file = $assessment->linkedFile($src);

        $uri = $assessment->url . '/' .$src;
        $code = substr(get_headers($uri)[0], 9, 3);

        if ($errors = base64_decode($file->getAttribute('errors'))) {
          if ($errors == 'NA') {
              $report = 'not-found';
              $count = "fix";
          } else {
            $report = json_decode($errors);
            $count = $report->cssvalidation->result->errorcount;
          }
        } else {
          $count = "NA";
        }

        $quality = new \bloc\types\Dictionary([
          'rules' => 'Total number of rules authored',
          'selectors' => 'Total Selectors',
          'selectorsByAttribute' => 'Attribute Selectors',
          'selectorsByClass' => 'Class selectors',
          'selectorsByTag' => 'Type (Element) selectors',
          'selectorsById' => 'ID selectors',
          'selectorsByPseudo' => 'Pseudo selectors',
          'qualifiedSelectors' => 'Qualified Selectors',
          'colors' => 'The number of colors specified',
          'mediaQueries' => 'Number of media queries',
          'complexSelectors' => 'Complex Selectors',
        ]);
        
        $stats = json_decode($file->getAttribute('stats'))->metrics;
       
        $files[] = [
          'url'     => base64_encode($uri),
          'name'    => $file->getAttribute('name'),
          'content' => null,
          'type'    => 'lang-css',
          'console' => $quality->map(function($item, $key) use($stats) {
            return ['name' => $item, 'value' => $stats->{$key} ];
          }),
          'report'  =>  ['sloc' => $file->getAttribute('sloc'), 'count' => $count, 'errors' => (new \bloc\types\Dictionary($report->cssvalidation->errors ?? []))->map(function ($item) {
            return ['line' => $item->line , 'message' => $item->message, 'type' => ''];
          })],
        ];
      }
      return $files;
    }


    public function getEvaluation($evaluation, $course = "*")
    {
      $reviewed = $this->student->context->find("{$evaluation}[@updated]");
      $total    = $reviewed->count();
      $scores   = [];
      $flags    = ["⚐" => 0, "✗" => 0];

      $collect = Criterion::collect(function ($criterion, $index) use($evaluation, $total, $reviewed, &$flags, &$scores) {
        $node = $reviewed->pick($index) ?? $this->student->context->getFirst($evaluation, $index);
        $map = [
          $evaluation => Data::FACTORY($evaluation, $node, null, [new \models\Criterion($criterion), $this->student]),
          'schedule'  => $this->student->section->schedule[$criterion['@assigned'] ?: $index ],
          'due'       => $this->student->section->schedule[$criterion['@due'] ?: $index ],
        ];
        
        $score = $map[$evaluation]->score;
        if ($evaluation === 'project') {
          $score             = $map[$evaluation]->score;
          $critique          = $map[$evaluation]->critique;
          $stats             = $this->collective($this->student->section, $criterion, 'critique');
          $z                 = ($critique - $stats['mean']) / ($stats['sd'] ?: 1);
          $stats['score']    = round($stats['wmean'] + ($z * $stats['sd']), 2);
          $stats['standard'] = $score * 100;
          $map['stats']      = $map[$evaluation]->stats = $stats;
        }
        
        if ($map[$evaluation]->status != 'open') {
          $scores[] = $score;
        }
        
        
        return $map;

      }, "[@type='{$evaluation}' and (@course = '{$course}' or @course = '*')]");

      $weight = Assessment::$weight[$evaluation];
      $list   = iterator_to_array($collect, false);
      
      // if necessary I'll drop the practice
      // if ($evaluation == 'practice') {
      //   sort($scores);
      //   unset($scores[0]);
      // }
      
      $avg = array_sum($scores) / (count($scores) ?: 1);
      
      return new \bloc\types\dictionary([
        'list'   => $list,
        'score'  => max(0, round($avg * $weight, 1)),
        'weight' => $weight,
      ]);
    }
}
