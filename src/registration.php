<?php
/**
 * only needed when your doing non-composer install into lib/internal/
 */
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::LIBRARY,
    'bold/pimservice',
    __DIR__
);