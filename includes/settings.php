<?php


    const DEV_MODE = FALSE; //FALSE = Production
    const ERR_IDENTIFIER = 'radSYS_error~';

    /**
     * Application Settings
     */

    const DEFAULT_CONTROLLER = 'home';
    const DEFAULT_METHOD     = 'main';
    const DEFAULT_LANGUAGE   = 'en';


    /* database information */
    const DB_NAME = 'test';
    const DB_USER = 'root';
    const DB_PASS = 'root';
    const DB_HOST = '127.0.0.1';


    /* general settings */

    const DEFAULT_REDIRECT_TIME = 2000;
    const CACHE_DIR = '../includes/syscache';
    const COOKIE_PATH = '/';
    const COOKIE_DOMAIN = '';

    const DEFAULT_SCHEME = 'http';

    /* cache settings */
    const OUTPUT_COMPRESSION = FALSE;


    $_APP['website_name'] = 'RADSYS 4.21.10';
