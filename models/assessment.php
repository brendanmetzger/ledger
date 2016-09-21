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
      'practice'  => 35,
      'quiz'      => 10,
      'project'   => 25,
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

    private function collective(Section $section, $c)
    {
      if (! $storage = self::$storage) {
        $storage = self::$storage = new \bloc\Maybe([]);
      }

      $key = $c['@type'].$c['@index'].$section['@id'].$section['@course'];

      return $storage($key)->get(function ($key) use($section, $c) {
        $scores = [];
        foreach ($section->students as $item) {
          $context = $item['student']->context->getElement($c['@type'], $c['@index']);
          $score = Data::FACTORY($c['@type'], $context)->loadCriterion($c)->score;
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

    static public function LETTER($score, $multiplier = 1)
    {
      foreach (self::$rubric as $letter => $threshold) {
        if ($score > ($threshold * $multiplier)) {
          return $letter;
        }
      }
      return '-';
    }

    static public function LINKS($url)
    {
      $content = file_get_contents($url);
      $doc = new \DOMDocument();
      $doc->loadHTML($content);
      // \bloc\application::instance()->log($doc);
      $xpath = new \DOMXpath($doc);
      $files = [
        [
          'name'    => 'index.html',
          'content' => $content,
          'type'    => 'lang-html',
          'url'     => $url
        ],
        [
          'name'    => 'README',
          'content' => null,
          'type'    => 'plain-text',
          'url'     => base64_encode($url . '/readme.txt'),
        ]
      ];
      foreach ($xpath->query("//script[@src and not(contains(@src, 'http'))]") as $file) {
        $src = $file->getAttribute('src');
        $files[] = [
          'url'     => base64_encode($url . '/' .$src),
          'name'    => substr($src, strrpos($src, '/') + 1),
          'content' => null,
          'type'    => 'lang-js',
        ];
      }
      foreach ($xpath->query("//link[not(contains(@href, 'http'))]") as $file) {
        $src = $file->getAttribute('href');
        $files[] = [
          'url'     => base64_encode($url . '/' .$src),
          'name'    => substr($src, strrpos($src, '/') + 1),
          'content' => null,
          'type'    => 'lang-css'
        ];
      }
      return $files;
    }


    public function getEvaluation($evaluation, $course = "*")
    {
      $reviewed = $this->student->context->find($evaluation);
      $total = $reviewed->count();
      $average  = 1 / ($total ?: 1);
      $accumulator = 0;

      $collect = Criterion::collect(function ($criterion, $index) use($evaluation, $total, $reviewed, $average, &$accumulator) {
        $map = [
          $evaluation => Data::FACTORY($evaluation, $reviewed->pick($index))->loadCriterion($criterion),
          'schedule'  => $this->student->section->schedule[$criterion['@assigned'] ?: $index],
          'due'       => $this->student->section->schedule[$criterion['@due'] ?: $index],
        ];
        $score = $map[$evaluation]->score;
        if (($evaluation === 'quiz' || $evaluation === 'project') && $total > $criterion['@index']) {
          $stats = $this->collective($this->student->section, $criterion);
          if ($score > 0 && $stats['sd'] > 0) {
            $z = ($score - $stats['mean']) / $stats['sd'];
            $score = round($stats['wmean'] + ($z * $stats['sd']), 2);
          }
          $stats['standard'] = $score * 100;
          $map['stats'] = $stats;

        }
        $accumulator = ($accumulator + ($score * $average));
        return $map;
      }, "[@type='{$evaluation}' and @course = '{$course}']");

      $weight = Assessment::$weight[$evaluation];
      return new \bloc\types\dictionary([
        'list'   => iterator_to_array($collect, false),
        'score'  => $average === 1 && $total == 0 ? $weight : max(0, round($accumulator * $weight, 1)),
        'weight' => $weight,
      ]);
    }
}
