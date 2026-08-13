<?php

namespace Neblabs\HeaderInjector;

use PHPUnit\Framework\TestCase;

/*
 * A simple integration test uses the whole environment
 */
class HeaderInjectorCommandTest extends TestCase
{
    protected static array $git_dirs = [
        'tests/fixtures/git-dir',
        'tests/fixtures/git-dir-unstable-tags',
    ];
    public static function setUpBeforeClass(): void
    {
        // add the git dirs
        foreach (static::$git_dirs as $git_dir) {
            if (is_dir("$git_dir/.git")) {
                return;
            }
            rename(from: "$git_dir/git", to: "$git_dir/.git");
        }
    }
    public static function tearDownAfterClass(): void
    {
        // add the git dirs
        foreach (static::$git_dirs as $git_dir) {
            if (is_dir("$git_dir/git")) {
                return;
            }
            rename(from: "$git_dir/.git", to: "$git_dir/git");
        }
    }

    public function test_with_a_different_git_dir_from_the_source()
    {
        $command = new HeaderInjectorCommand;

        $source = __DIR__ . '/fixtures/in';
        $target = sys_get_temp_dir() . '/' . microtime();
        $gitDir = __DIR__ . '/fixtures/git-dir';
        $expectedDir = __DIR__ . '/fixtures/out';

        $command(source: $source, target: $target, gitDir: $gitDir, wpTestedVersion: 6.8, silent: true);

        $targetPHPFile = "$target/coupons-plus-for-woocommerce.php";
        $targetMDFile = "$target/readme.md";

        $this->assertFileEquals("$expectedDir/index.php", $targetPHPFile, $targetPHPFile);
        $this->assertFileEquals("$expectedDir/readme.md", $targetMDFile, $targetMDFile);
    }

    # i know what youre thinking but no these are simple requirements and creating a whole ahh dir for each is overkill; this is the pragmatic approach.
    public function test_unknown_is_removed_when_no_value_comma_empty_returned_dynamic_values_are_skipped_comma_and_non_empty_dynamic_values_are_added_if_not_present_in_the_source()
    {
        $command = new HeaderInjectorCommand;

        $source = __DIR__ . '/fixtures/in-mixed';
        $target = sys_get_temp_dir() . '/' . microtime();
        $gitDir = __DIR__ . '/fixtures/git-dir';
        $expectedDir = __DIR__ . '/fixtures/out-mixed';

        $command(source: $source, target: $target, gitDir: $gitDir, wpTestedVersion: 6.8, silent: true);

        $targetFile = "$target/coupons-plus-for-woocommerce.php";

        $this->assertFileEquals("$expectedDir/index.php", $targetFile, $targetFile);
    }

    /*
     * In this test the latest tag is rc-1. the plugin header should get that and the read me the stable 1.1.0
     */
    public function test_with_unstable_tags()
    {
        $command = new HeaderInjectorCommand;

        $source = __DIR__ . '/fixtures/in';
        $target = sys_get_temp_dir() . '/' . microtime();
        $gitDir = __DIR__ . '/fixtures/git-dir-unstable-tags';
        $expectedDir = __DIR__ . '/fixtures/out-unstable';

        $command(source: $source, target: $target, gitDir: $gitDir, wpTestedVersion: 6.8, silent: true);

        $targetPHPFile = "$target/coupons-plus-for-woocommerce.php";
        $targetMDFile = "$target/readme.md";

        $this->assertFileEquals("$expectedDir/index.php", $targetPHPFile, $targetPHPFile);
        $this->assertFileEquals("$expectedDir/readme.md", $targetMDFile, $targetMDFile);
    }

    public function test_as_cli()
    {
        $source = __DIR__ . '/fixtures/in';
        $target = sys_get_temp_dir() . '/' . microtime();
        $gitDir = __DIR__ . '/fixtures/git-dir';
        $expectedDir = __DIR__ . '/fixtures/out';

        //$command(source: $source, target: $target, gitDir: $gitDir, wpTestedVersion: 6.8, silent: true);

        $rootDir = dirname(__FILE__, 2);
        $injector = "$rootDir/bin/header-injector";

        system("php $injector inject '$source' '$target' 6.8 --git-source '$gitDir' --silent");

        $targetPHPFile = "$target/coupons-plus-for-woocommerce.php";
        $targetMDFile = "$target/readme.md";

        $this->assertFileEquals("$expectedDir/index.php", $targetPHPFile, $targetPHPFile);
        $this->assertFileEquals("$expectedDir/readme.md", $targetMDFile, $targetMDFile);
    }

}
