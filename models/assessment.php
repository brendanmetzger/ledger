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
      'practice'  => 40,
      'quiz'      => 10,
      'project'   => 20,
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



    private $context, $schedule;

    public function __construct(Student $student)
    {
      $this->context  = $student->context;
      $this->schedule = $student->section->schedule;
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

    public function getEvaluation($evaluation, $query)
    {
      $reviewed = $this->context->find($evaluation);
      $average  = 1 / ($reviewed->count() ?: 1);
      $accumulator = 0;

      $collect = Criterion::collect(function ($criterion, $index) use($evaluation, $reviewed, $average, &$accumulator) {
        $map = [
          $evaluation => Data::FACTORY($evaluation, $reviewed->pick($index)),
          'criterion' => $criterion,
          'schedule'  => $this->schedule[$criterion['@assigned'] ?: $index],
          'due'       => $this->schedule[$criterion['@due'] ?: $index],
        ];
        $accumulator = ($accumulator + ($map[$evaluation]->score * $average));
        return $map;
      }, $query);
      $weight = Assessment::$weight[$evaluation];

      return new \bloc\types\dictionary([
        'list'   => iterator_to_array($collect, false),
        'score'  => $average === 1 ? $weight : max(0, round($accumulator * $weight, 1)),
        'weight' => $weight,
      ]);
    }

    // get total.
    // get individual(type)
}
