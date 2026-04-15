<?php

/**
 * Home Module — Controllers
 */

function home_index(): void
{
    view('home::index', [
        'content_container' => false,
        'page_title' => 'Home',
    ], 'main');
}
