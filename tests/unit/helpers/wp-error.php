<?php

declare(strict_types=1);

// The class intentionally mirrors the WordPress core class name and its
// snake_case method names, which do not follow PSR-12.
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps, PSR1.Methods.CamelCapsMethodName.NotCamelCaps
if (!\class_exists('WP_Error')) {
    /**
     * Minimal WP_Error stub for unit tests.
     */
    class WP_Error
    {
        /**
         * @var array<string, string[]>
         */
        private array $errors = [];

        public function __construct(string $code = '', string $message = '')
        {
            if ($code !== '') {
                $this->errors[$code][] = $message;
            }
        }

        public function get_error_code(): string
        {
            return (string) \array_key_first($this->errors);
        }

        public function get_error_message(string $code = ''): string
        {
            if ($code === '') {
                $code = $this->get_error_code();
            }

            return $this->errors[$code][0] ?? '';
        }
    }
}
