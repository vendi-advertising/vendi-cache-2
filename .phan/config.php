<?php

/**
 * This configuration will be read and overlaid on top of the
 * default configuration. Command line arguments will be applied
 * after this file is read.
 */
return [

    'skip_slow_php_options_warning' => true,

    'backward_compatibility_checks' => true,

    'analyze_signature_compatibility' => true,

    'allow_missing_properties' => false,

    'null_casts_as_any_type' => false,

    'null_casts_as_array' => false,

    'array_casts_as_null' => false,

    'scalar_implicit_cast' => false,

    'ignore_undeclared_variables_in_global_scope' => false,

    'dead_code_detection' => true,


    'file_list' => [
        'vendi-cache.php',
    ],

    // A list of directories that should be parsed for class and
    // method information. After excluding the directories
    // defined in exclude_analysis_directory_list, the remaining
    // files will be statically analyzed for errors.
    //
    // Thus, both first-party and third-party code being used by
    // your application should be included in this list.
    'directory_list' => [
        'cli/',
        'src/',
        'includes/',
        'vendor/',
        // 'vendor/symfony/console',
    ],

    // A directory list that defines files that will be excluded
    // from static analysis, but whose class and method
    // information should be included.
    //
    // Generally, you'll want to include the directories for
    // third-party code (such as "vendor/") in this list.
    //
    // n.b.: If you'd like to parse but not analyze 3rd
    //       party code, directories containing that code
    //       should be added to both the `directory_list`
    //       and `exclude_analysis_directory_list` arrays.
    "exclude_analysis_directory_list" => [
        'vendor/',
    ],
];
