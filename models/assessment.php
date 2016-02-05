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

    private $context, $schedule;

    public function __construct(Student $student)
    {
      $this->context  = $student->context;
      $this->schedule = $student->section->schedule;
    }

    static public function LINKS($url)
    {
      $content = file_get_contents($url);
      $doc = new \DOMDocument();
      $doc->loadHTML($content);
      // \bloc\application::instance()->log($doc);
      $xpath = new \DOMXpath($doc);
      $files = [[
        'name' => 'index.html',
        'content' => $content,
      ]];
      foreach ($xpath->query("//script[@src and not(contains(@src, 'http'))]") as $file) {
        $src = $file->getAttribute('src');
        $files[] = [
          'url'     => base64_encode($url . '/' .$src),
          'name'    => substr($src, strrpos($src, '/') + 1),
          'content' => null,
        ];
      }
      foreach ($xpath->query("//link[not(contains(@href, 'http'))]") as $file) {
        $src = $file->getAttribute('href');
        $files[] = [
          'url'     => base64_encode($url . '/' .$src),
          'name'    => substr($src, strrpos($src, '/') + 1),
          'content' => null,
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
          'schedule'  => $this->schedule[$index]
        ];

        $accumulator = ($accumulator + ($map[$evaluation]->score * $average));
        return $map;
      }, $query);

      return new \bloc\types\dictionary([
        'list' => iterator_to_array($collect, false),
        'score' => max(0, ($accumulator * Assessment::$weight[$evaluation]) . '‰')
      ]);
    }

    // get total.
    // get individual(type)

}
