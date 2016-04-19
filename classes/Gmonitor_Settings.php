<?php
class Gmonitor_Settings {

    /**
     * AJAX page refresh interval, ms
     * @var int
     */
    public static $refresh_interval = 2000;

    /**
     * The number of the last lines of the log file is displayed when the page is load
     * The following lines are added to these
     * @var int
     */
    public static $initial_count_log_rows = 10;

    /**
     * Synonyms of functions names
     * Will be displayed in the table instead of names
     * Array key: name of the function
     * value: displayed synonym (can be in any language, with spaces etc.)
     * @var array
     */
    public static $func_name_synonyms = array(
        'page_with_offer_get' => 'Страницы предложений',
        'page_with_items_get' => 'Страницы товаров',
    );

    /**
     * If false, displays all functions registered on the server
     * If true, displayed only functions which have synonym
     * This property is necessary for the situation when the server are processed several projects
     * And on server registered functions of different projects,
     * but we want to monitor only our funcions (see examples)
     * @var bool
     */
    public static $synonyms_only_view = false;
}
 
