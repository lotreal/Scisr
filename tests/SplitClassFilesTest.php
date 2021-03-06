<?php
require_once 'SingleFileTest.php';

class SplitClassFilesTest extends Scisr_SingleFileTest
{

  protected $function = "/**
 * Some func comment
 */
function someFunc(\$args) {
  /* a comment !*/
};";
  protected $baz = "class Baz {
    function bar() {
    }
}";
protected $bar = "class Bar {
    function bar() {
    }
}";
    protected $start = "<?php \n ";

    protected $comment = "/** this is a comment about a class */";


    private $outputDir;
    public function setUp() {
        parent::setUp();
        $this->outputDir = dirname(__FILE__) . "/" . "_tmp" . __CLASS__;
        /* reset test area */
        $this->delTree($this->outputDir) ;
        mkdir($this->outputDir);

    }
    public function splitAndCompare($expected, $original, $aggressive=false) {
        $this->populateFile($original);

        $s = $this->getScisr();
        if ($aggressive) {
            $s->setEditMode(ScisrRunner::MODE_AGGRESSIVE);
        }


        $s->setSplitClassFiles($this->outputDir);
        $s->addFile($this->test_file);
        $s->run();

        foreach ($expected as $filename => $content) {
            $actual = file_get_contents($this->outputDir . "/" . $filename . ".php" );
            $this->assertEquals("<?php\n" . $content, $actual);
        }
    }
    public function testSplitFilesTwoClasses() {
        $orig = "{$this->start}{$this->baz}\n{$this->bar}";
        $expected = array(
            'Baz' => $this->baz . "\n", 'Bar' => $this->bar . "\n" );
        $this->splitAndCompare($expected, $orig);
    }
    public function testSplitFilesTwoClassesNotCommentBetween() {
        $orig = "{$this->start}
{$this->comment}
{$this->baz}\n
{$this->bar}";
        $expected = array(
            'Baz' => $this->comment . "\n" . $this->baz . "\n",
            'Bar' => $this->bar . "\n"
            );
        $this->splitAndCompare($expected, $orig);
    }



    public function testSplitFilesTwoClassesWithComments() {
        $orig = "{$this->start}
{$this->comment}
{$this->baz}\n
{$this->comment}
{$this->bar}";
        $expected = array(
            'Baz' => $this->comment . "\n" . $this->baz . "\n",
            'Bar' => $this->comment . "\n". $this->bar . "\n"
            );
        $this->splitAndCompare($expected, $orig);
    }

    public function testSplitFilesTwoClassesWithCommentsAndAFunctionOnTop() {
        $orig = "{$this->start}
{$this->function}
{$this->comment}
{$this->baz}\n
{$this->function}
{$this->comment}
{$this->bar}";
        $expected = array(
            'Baz' => $this->comment . "\n" . $this->baz . "\n",
            'Bar' => $this->comment . "\n". $this->bar . "\n"
            );
        $this->splitAndCompare($expected, $orig);
    }

    public function testOverwriteExisting() {
        $orig = "{$this->start}
{$this->function}
{$this->comment}
{$this->baz}\n
{$this->function}
{$this->comment}
{$this->bar}";
        $expected = array(
            'Baz' => $this->comment . "\n" . $this->baz . "\n",
            'Bar' => $this->comment . "\n". $this->bar . "\n"
            );
        touch($this->outputDir . "/Baz.php");
        $this->splitAndCompare($expected, $orig, true);

    }
    /**
     * @expectedException RuntimeException
     */
    public function testOverwriteExistingThrowsExceptionIfNotAggressive() {
        $orig = "{$this->start}
{$this->function}
{$this->comment}
{$this->baz}\n
{$this->function}
{$this->comment}
{$this->bar}";
        $expected = array(
            'Baz' => $this->comment . "\n" . $this->baz . "\n",
            'Bar' => $this->comment . "\n". $this->bar . "\n"
            );
        touch($this->outputDir . "/Baz.php");
        $this->splitAndCompare($expected, $orig, false);

    }

  private function delTree($dir) {
      if ($dir == "" || $dir == null || $dir == false) {
        throw new Exception("delTree: bad directory path!");
      }

      $files = glob( $dir . '*', GLOB_MARK );
      foreach( $files as $file ){
          if( substr( $file, -1 ) == '/' )
              $this->delTree( $file );
          else
              unlink( $file );
      }

      if (is_dir($dir)) rmdir( $dir );

  }

}
