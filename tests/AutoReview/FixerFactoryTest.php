<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\AutoReview;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerFactory;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @coversNothing
 * @group auto-review
 */
final class FixerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFixersPriorityEdgeFixers()
    {
        $factory = new FixerFactory();
        $factory->registerBuiltInFixers();
        $fixers = $factory->getFixers();

        $this->assertSame('encoding', $fixers[0]->getName());
        $this->assertSame('full_opening_tag', $fixers[1]->getName());
        $this->assertSame('single_blank_line_at_eof', $fixers[count($fixers) - 1]->getName());
    }

    /**
     * @dataProvider getFixersPriorityCases
     */
    public function testFixersPriority(FixerInterface $first, FixerInterface $second)
    {
        $this->assertLessThan($first->getPriority(), $second->getPriority());
    }

    public function getFixersPriorityCases()
    {
        $factory = new FixerFactory();
        $factory->registerBuiltInFixers();

        $fixers = [];

        foreach ($factory->getFixers() as $fixer) {
            $fixers[$fixer->getName()] = $fixer;
        }

        $cases = [
            [$fixers['elseif'], $fixers['braces']],
            [$fixers['method_separation'], $fixers['braces']],
            [$fixers['method_separation'], $fixers['indentation_type']],
            [$fixers['no_leading_import_slash'], $fixers['ordered_imports']], // tested also in: no_leading_import_slash,ordered_imports.test
            [$fixers['no_multiline_whitespace_around_double_arrow'], $fixers['binary_operator_spaces']], // tested also in: no_multiline_whitespace_around_double_arrow,binary_operator_spaces.test
            [$fixers['no_multiline_whitespace_around_double_arrow'], $fixers['trailing_comma_in_multiline_array']], // tested also in: no_multiline_whitespace_around_double_arrow,trailing_comma_in_multiline_array.test
            [$fixers['no_php4_constructor'], $fixers['ordered_class_elements']], // tested also in: no_php4_constructor,ordered_class_elements.test
            [$fixers['no_short_bool_cast'], $fixers['cast_spaces']], // tested also in: no_short_bool_cast,cast_spaces.test
            [$fixers['no_short_echo_tag'], $fixers['no_mixed_echo_print']], // tested also in: no_mixed_echo_print,no_short_echo_tag.test
            [$fixers['indentation_type'], $fixers['phpdoc_indent']],
            [$fixers['no_unneeded_control_parentheses'], $fixers['no_trailing_whitespace']], // tested also in: no_trailing_whitespace,no_unneeded_control_parentheses.test
            [$fixers['no_unused_imports'], $fixers['blank_line_after_namespace']], // tested also in: no_unused_imports,blank_line_after_namespace.test and no_unused_imports,blank_line_after_namespace_2.test
            [$fixers['no_unused_imports'], $fixers['no_extra_consecutive_blank_lines']], // tested also in: no_unused_imports,no_extra_consecutive_blank_lines.test
            [$fixers['no_unused_imports'], $fixers['no_leading_import_slash']], // no priority issue; for speed only
            [$fixers['ordered_class_elements'], $fixers['method_separation']], // tested also in: ordered_class_elements,method_separation.test
            [$fixers['ordered_class_elements'], $fixers['no_blank_lines_after_class_opening']], // tested also in: ordered_class_elements,no_blank_lines_after_class_opening.test
            [$fixers['ordered_class_elements'], $fixers['space_after_semicolon']], // tested also in: ordered_class_elements,space_after_semicolon.test
            [$fixers['php_unit_strict'], $fixers['php_unit_construct']],
            [$fixers['phpdoc_no_access'], $fixers['phpdoc_order']],
            [$fixers['phpdoc_no_access'], $fixers['phpdoc_separation']],
            [$fixers['phpdoc_no_access'], $fixers['phpdoc_trim']],
            [$fixers['phpdoc_no_empty_return'], $fixers['phpdoc_order']], // tested also in: phpdoc_no_empty_return,phpdoc_separation.test
            [$fixers['phpdoc_no_empty_return'], $fixers['phpdoc_separation']], // tested also in: phpdoc_no_empty_return,phpdoc_separation.test
            [$fixers['phpdoc_no_empty_return'], $fixers['phpdoc_trim']],
            [$fixers['phpdoc_no_package'], $fixers['phpdoc_order']],
            [$fixers['phpdoc_no_package'], $fixers['phpdoc_separation']], // tested also in: phpdoc_no_package,phpdoc_separation.test
            [$fixers['phpdoc_no_package'], $fixers['phpdoc_trim']],
            [$fixers['phpdoc_order'], $fixers['phpdoc_separation']],
            [$fixers['phpdoc_order'], $fixers['phpdoc_trim']],
            [$fixers['phpdoc_separation'], $fixers['phpdoc_trim']],
            [$fixers['phpdoc_summary'], $fixers['phpdoc_trim']],
            [$fixers['phpdoc_var_without_name'], $fixers['phpdoc_trim']],
            [$fixers['pow_to_exponentiation'], $fixers['binary_operator_spaces']], // tested also in: pow_to_exponentiation,binary_operator_spaces.test
            [$fixers['pow_to_exponentiation'], $fixers['method_argument_space']], // no priority issue; for speed only
            [$fixers['pow_to_exponentiation'], $fixers['native_function_casing']], // no priority issue; for speed only
            [$fixers['pow_to_exponentiation'], $fixers['no_spaces_after_function_name']], // no priority issue; for speed only
            [$fixers['pow_to_exponentiation'], $fixers['no_spaces_inside_parenthesis']], // no priority issue; for speed only
            [$fixers['single_import_per_statement'], $fixers['ordered_imports']], // tested also in: single_import_per_statement,ordered_imports.test
            [$fixers['single_import_per_statement'], $fixers['no_singleline_whitespace_before_semicolons']], // tested also in: single_import_per_statement,no_singleline_whitespace_before_semicolons.test
            [$fixers['single_import_per_statement'], $fixers['space_after_semicolon']], // tested also in: single_import_per_statement,space_after_semicolon.test
            [$fixers['single_import_per_statement'], $fixers['no_multiline_whitespace_before_semicolons']], // single_import_per_statement,no_multiline_whitespace_before_semicolons.test
            [$fixers['single_import_per_statement'], $fixers['no_leading_import_slash']], // tested also in: single_import_per_statement,no_leading_import_slash.test
            [$fixers['single_import_per_statement'], $fixers['no_unused_imports']], // tested also in: single_import_per_statement,no_unused_imports.test
            [$fixers['unary_operator_spaces'], $fixers['not_operator_with_space']],
            [$fixers['unary_operator_spaces'], $fixers['not_operator_with_successor_space']],
            [$fixers['line_ending'], $fixers['single_blank_line_at_eof']],
            [$fixers['simplified_null_return'], $fixers['no_useless_return']], // tested also in: simplified_null_return,no_useless_return.test
            [$fixers['no_useless_return'], $fixers['no_whitespace_in_blank_line']], // tested also in: no_useless_return,no_whitespace_in_blank_line.test
            [$fixers['no_useless_return'], $fixers['no_extra_consecutive_blank_lines']], // tested also in: no_useless_return,no_extra_consecutive_blank_lines.test
            [$fixers['no_useless_return'], $fixers['blank_line_before_return']], // tested also in: no_useless_return,blank_line_before_return.test
            [$fixers['no_empty_phpdoc'], $fixers['no_extra_consecutive_blank_lines']], // tested also in: no_empty_phpdoc,no_extra_consecutive_blank_lines.test
            [$fixers['no_empty_phpdoc'], $fixers['no_trailing_whitespace']], // tested also in: no_empty_phpdoc,no_trailing_whitespace.test
            [$fixers['no_empty_phpdoc'], $fixers['no_whitespace_in_blank_line']], // tested also in: no_empty_phpdoc,no_whitespace_in_blank_line.test
            [$fixers['phpdoc_no_access'], $fixers['no_empty_phpdoc']], // tested also in: phpdoc_no_access,no_empty_phpdoc.test
            [$fixers['phpdoc_no_empty_return'], $fixers['no_empty_phpdoc']], // tested also in: phpdoc_no_empty_return,no_empty_phpdoc.test
            [$fixers['phpdoc_no_package'], $fixers['no_empty_phpdoc']], // tested also in: phpdoc_no_package,no_empty_phpdoc.test
            [$fixers['combine_consecutive_unsets'], $fixers['space_after_semicolon']], // tested also in: combine_consecutive_unsets,space_after_semicolon.test
            [$fixers['combine_consecutive_unsets'], $fixers['no_whitespace_in_blank_line']], // tested also in: combine_consecutive_unsets,no_whitespace_in_blank_line.test
            [$fixers['combine_consecutive_unsets'], $fixers['no_trailing_whitespace']], // tested also in: combine_consecutive_unsets,no_trailing_whitespace.test
            [$fixers['combine_consecutive_unsets'], $fixers['no_extra_consecutive_blank_lines']], // tested also in: combine_consecutive_unsets,no_extra_consecutive_blank_lines.test
            [$fixers['phpdoc_no_alias_tag'], $fixers['phpdoc_single_line_var_spacing']], // tested also in: phpdoc_no_alias_tag,phpdoc_single_line_var_spacing.test
            [$fixers['blank_line_after_opening_tag'], $fixers['no_blank_lines_before_namespace']], // tested also in: blank_line_after_opening_tag,no_blank_lines_before_namespace.test
            [$fixers['phpdoc_to_comment'], $fixers['no_empty_comment']], // tested also in: phpdoc_to_comment,no_empty_comment.test
            [$fixers['no_empty_comment'], $fixers['no_extra_consecutive_blank_lines']], // tested also in: no_empty_comment,no_extra_consecutive_blank_lines.test
            [$fixers['no_empty_comment'], $fixers['no_trailing_whitespace']], // tested also in: no_empty_comment,no_trailing_whitespace.test
            [$fixers['no_empty_comment'], $fixers['no_whitespace_in_blank_line']], // tested also in: no_empty_comment,no_whitespace_in_blank_line.test
            [$fixers['no_alias_functions'], $fixers['php_unit_dedicate_assert']], // tested also in: no_alias_functions,php_unit_dedicate_assert.test
            [$fixers['no_empty_statement'], $fixers['braces']],
            [$fixers['no_empty_statement'], $fixers['combine_consecutive_unsets']], // tested also in: no_empty_statement,combine_consecutive_unsets.test
            [$fixers['no_empty_statement'], $fixers['no_extra_consecutive_blank_lines']], // tested also in: no_empty_statement,no_extra_consecutive_blank_lines.test
            [$fixers['no_empty_statement'], $fixers['no_multiline_whitespace_before_semicolons']],
            [$fixers['no_empty_statement'], $fixers['no_singleline_whitespace_before_semicolons']],
            [$fixers['no_empty_statement'], $fixers['no_trailing_whitespace']], // tested also in: no_empty_statement,no_trailing_whitespace.test
            [$fixers['no_empty_statement'], $fixers['no_useless_else']], // tested also in: no_empty_statement,no_useless_else.test
            [$fixers['no_empty_statement'], $fixers['no_useless_return']], // tested also in: no_empty_statement,no_useless_return.test
            [$fixers['no_empty_statement'], $fixers['no_whitespace_in_blank_line']], // tested also in: no_empty_statement,no_whitespace_in_blank_line.test
            [$fixers['no_empty_statement'], $fixers['space_after_semicolon']], // tested also in: no_empty_statement,space_after_semicolon.test
            [$fixers['no_empty_statement'], $fixers['switch_case_semicolon_to_colon']], // tested also in: no_empty_statement,switch_case_semicolon_to_colon.test
            [$fixers['no_useless_else'], $fixers['braces']],
            [$fixers['no_useless_else'], $fixers['combine_consecutive_unsets']], // tested also in: no_useless_else,combine_consecutive_unsets.test
            [$fixers['no_useless_else'], $fixers['no_extra_consecutive_blank_lines']], // tested also in: no_useless_else,no_extra_consecutive_blank_lines.test
            [$fixers['no_useless_else'], $fixers['no_useless_return']], // tested also in: no_useless_else,no_useless_return.test
            [$fixers['no_useless_else'], $fixers['no_trailing_whitespace']], // tested also in: no_useless_else,no_trailing_whitespace.test
            [$fixers['no_useless_else'], $fixers['no_whitespace_in_blank_line']], // tested also in: no_useless_else,no_whitespace_in_blank_line.test
            [$fixers['declare_strict_types'], $fixers['single_blank_line_before_namespace']], // tested also in: declare_strict_types,single_blank_line_before_namespace.test
            [$fixers['declare_strict_types'], $fixers['blank_line_after_opening_tag']], // tested also in: declare_strict_types,blank_line_after_opening_tag.test
            [$fixers['array_syntax'], $fixers['binary_operator_spaces']], // tested also in: array_syntax,binary_operator_spaces.test
            [$fixers['array_syntax'], $fixers['ternary_operator_spaces']], // tested also in: array_syntax,ternary_operator_spaces.test
            [$fixers['class_keyword_remove'], $fixers['no_unused_imports']], // tested also in: class_keyword_remove,no_unused_imports.test
            [$fixers['no_blank_lines_after_phpdoc'], $fixers['single_blank_line_before_namespace']], // tested also in: no_blank_lines_after_phpdoc,single_blank_line_before_namespace.test
            [$fixers['php_unit_fqcn_annotation'], $fixers['no_unused_imports']], // tested also in: php_unit_fqcn_annotation,unused_use.test
            [$fixers['protected_to_private'], $fixers['ordered_class_elements']], // tested also in: protected_to_private,ordered_class_elements.test
            [$fixers['phpdoc_add_missing_param_annotation'], $fixers['phpdoc_align']], // tested also in: phpdoc_add_missing_param_annotation,phpdoc_align.test
            [$fixers['phpdoc_no_alias_tag'], $fixers['phpdoc_add_missing_param_annotation']], // tested also in: phpdoc_no_alias_tag,phpdoc_add_missing_param_annotation.test
            [$fixers['phpdoc_no_useless_inheritdoc'], $fixers['no_empty_phpdoc']], // tested also in: phpdoc_no_useless_inheritdoc,no_empty_phpdoc.test
            [$fixers['phpdoc_no_useless_inheritdoc'], $fixers['no_trailing_whitespace_in_comment']], // tested also in: phpdoc_no_useless_inheritdoc,no_trailing_whitespace_in_comment.test
            [$fixers['phpdoc_no_useless_inheritdoc'], $fixers['phpdoc_inline_tag']], // tested also in: phpdoc_no_useless_inheritdoc,phpdoc_inline_tag.test
            [$fixers['phpdoc_to_comment'], $fixers['phpdoc_no_useless_inheritdoc']], // tested also in: phpdoc_to_comment,phpdoc_no_useless_inheritdoc.test
            [$fixers['declare_strict_types'], $fixers['declare_equal_normalize']], // tested also in: declare_strict_types,declare_equal_normalize.test
            [$fixers['phpdoc_add_missing_param_annotation'], $fixers['phpdoc_order']], // tested also in: phpdoc_add_missing_param_annotation,phpdoc_order.test
            [$fixers['no_spaces_after_function_name'], $fixers['function_to_constant']], // tested also in: no_spaces_after_function_name,function_to_constant.test
            [$fixers['no_spaces_inside_parenthesis'], $fixers['function_to_constant']], // tested also in: no_spaces_inside_parenthesis,function_to_constant.test
            [$fixers['function_to_constant'], $fixers['native_function_casing']], // no priority issue; for speed only
            [$fixers['function_to_constant'], $fixers['no_extra_consecutive_blank_lines']], // tested also in: function_to_constant,no_extra_consecutive_blank_lines.test
            [$fixers['function_to_constant'], $fixers['no_singleline_whitespace_before_semicolons']], // tested also in: function_to_constant,no_singleline_whitespace_before_semicolons.test
            [$fixers['function_to_constant'], $fixers['no_trailing_whitespace']], // tested also in: function_to_constant,no_trailing_whitespace.test
            [$fixers['function_to_constant'], $fixers['no_whitespace_in_blank_line']], // tested also in: function_to_constant,no_whitespace_in_blank_line.test
            [$fixers['list_syntax'], $fixers['binary_operator_spaces']], // tested also in: list_syntax,binary_operator_spaces.test
            [$fixers['list_syntax'], $fixers['ternary_operator_spaces']], // tested also in: list_syntax,ternary_operator_spaces.test
        ];

        // prepare bulk tests for phpdoc fixers to test that:
        // * `phpdoc_to_comment` is first
        // * `phpdoc_indent` is second
        // * `phpdoc_types` is third
        // * `phpdoc_scalar` is fourth
        // * `phpdoc_align` is last
        $cases[] = [$fixers['phpdoc_to_comment'], $fixers['phpdoc_indent']];
        $cases[] = [$fixers['phpdoc_indent'], $fixers['phpdoc_types']];
        $cases[] = [$fixers['phpdoc_types'], $fixers['phpdoc_scalar']];

        $docFixerNames = array_filter(
            array_keys($fixers),
            function ($name) {
                return false !== strpos($name, 'phpdoc');
            }
        );

        foreach ($docFixerNames as $docFixerName) {
            if (!in_array($docFixerName, ['phpdoc_to_comment', 'phpdoc_indent', 'phpdoc_types', 'phpdoc_scalar'], true)) {
                $cases[] = [$fixers['phpdoc_to_comment'], $fixers[$docFixerName]];
                $cases[] = [$fixers['phpdoc_indent'], $fixers[$docFixerName]];
                $cases[] = [$fixers['phpdoc_types'], $fixers[$docFixerName]];
                $cases[] = [$fixers['phpdoc_scalar'], $fixers[$docFixerName]];
            }

            if ('phpdoc_align' !== $docFixerName) {
                $cases[] = [$fixers[$docFixerName], $fixers['phpdoc_align']];
            }
        }

        return $cases;
    }
}
