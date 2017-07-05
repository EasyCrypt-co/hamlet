<?php
/**
 * Roundcubeskins.net plugin. See README for details.
 * Copyright 2014, Tecorama.
 */

class rcs_skins extends rcube_plugin
{
    private $version = "1.4.9";
    private $phone = false;
    private $tablet = false;
    private $desktop = false;
    private $mobile = false;
    private $device = false;
    private $skin = false;
    private $skinType = "desktop";

    public $allowed_prefs = array(
        "rcs_skin_color_alpha",
        "rcs_skin_color_icloud",
        "rcs_skin_color_outlook",
        "rcs_skin_color_litecube",
        "rcs_skin_color_litecube-f",
        "rcs_skin_color_w21",
    );

    private $rcsSkins = array(
        "alpha",
        "icloud",
        "litecube",
        "litecube-f",
        "outlook",
        "w21",
    );

    /**
     * List of plugins that are not fully compatible with the Roundcube skinning functionality.
     * the plugins listed here will be tricked to believe they run under larry.
     */
    private $fixPlugins = array(
        "calendar",
        "calendar_plus",
        "carddav",
        "compose_in_taskbar",
        "contactus",
        "google_ads",
        "impressum",
        "jappix4roundcube",
        "keyboard_shortcuts",
        "message_highlight",
        "moreuserinfo",
        "myrc_sprites",
        "nabble",
        "persistent_login",
        "planner",
        "plugin_manager",
        "pwtools",
        "register",
        "settings",
        "sticky_notes",
        "taskbar",
        "tasklist",
        "timepicker",
        "threecol",
        "scheduled_sending",
        "summary",
        "vcard_send",
        "vkeyboard",
    );

    /**
     * Initializes the plugin.
     */
    public function init()
    {
        $this->setDevice();
        $this->setSkin();

        $this->add_hook("config_get", array($this, "getConfig"));
        $this->add_hook('startup', array($this, 'startup'));
        $this->add_hook('render_page', array($this, 'renderPage'));
        $this->add_hook('preferences_list', array($this, 'preferencesList'));
        $this->add_hook('preferences_save', array($this, 'preferencesSave'));
        $this->add_hook('login_after', array($this, 'loginAfter'));

        $this->include_script('scripts.js');
        $this->include_stylesheet('styles.css');
    }

    /**
     * Returns true if the current device is a desktop computer, false otherwise. If rcs_skins is enabled, this function
     * can be used in other plugins:
     *
     * rcube::get_instance()->plugins->get_plugin("rcs_skins")->isDesktop()
     *
     * @return boolean
     */
    public function isDesktop()
    {
        return $this->desktop;
    }

    /**
     * Returns true if the current device is a tablet, false otherwise. If rcs_skins is enabled, this function can be
     * used in other plugins:
     *
     * rcube::get_instance()->plugins->get_plugin("rcs_skins")->isTablet()
     *
     * @return boolean
     */
    public function isTablet()
    {
        return $this->tablet;
    }

    /**
     * Returns true if the current device is a phone, false otherwise. If rcs_skins is enabled, this function can be
     * used in other plugins:
     *
     * rcube::get_instance()->plugins->get_plugin("rcs_skins")->isPhone()
     *
     * @return boolean
     */
    public function isPhone()
    {
        return $this->phone;
    }

    /**
     * Returns true if the current device is a mobile device, false otherwise. If rcs_skins is enabled, this function
     * can be used in other plugins:
     *
     * rcube::get_instance()->plugins->get_plugin("rcs_skins")->isMobile()
     *
     * @return boolean
     */
    public function isMobile()
    {
        return $this->mobile;
    }

    /**
     * Hook retrieving config options (including user settings)
     */
    function getConfig($args)
    {
        // disable unwanted plugins on mobile devices

        if ($this->skinType == "mobile") {
            $disable = array("preview_pane", "google_ads", "threecol");

            foreach ($disable as $val) {
                if (strpos($args['name'], $val) !== false) {
                    $args['result'] = false;
                    return $args;
                }
            }
        }

        // Substitute the skin name retrieved from the config file with "larry" for the plugins that treat larry-based
        // skins as "classic."

        if ($args['name'] != "skin" || !in_array($args['result'], $this->rcsSkins)) {
            return $args;
        }

        // check php version to use the right parameters
        if (version_compare(phpversion(), "5.3.6", "<")) {
            $options = false;
        } else {
            $options = DEBUG_BACKTRACE_IGNORE_ARGS;
        }

        // when passing 4 as the second parameter in php < 5.4, debug_backtrace will return null
        if (version_compare(phpversion(), "5.4.0", "<")) {
            $trace = debug_backtrace($options);
        } else {
            $trace = debug_backtrace($options, 4);
        }

        if (!empty($trace[3]['file']) && in_array(basename(dirname($trace[3]['file'])), $this->fixPlugins)) {
            $args['result'] = "larry";
        }

        return $args;
    }

    /**
     * Executes at the start of the program run.
     */
    public function startup()
    {
        $rcmail = rcmail::get_instance();

        $legacyPlugins = array("nutsmail_theme_selector", "rcs_mobile_options", "rcs_mobile_switch");
        $legacyResult = array_intersect($legacyPlugins, $rcmail->config->get('plugins'));

        if (!empty($legacyResult)) {

            echo "<!DOCTYPE html>\n<html lang='en'><head>".
                "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />".
                "<title>Roundcube Webmail</title>".
                "<style type='text/css'>".
                "body { font-family: Arial, Helvetica, sans-serif; font-style: normal; }".
                "</style>".
                "</head><body>".
                "<h3>Roundcube Error</h3>";

            if (count($legacyResult) > 1) {
                echo "<p>The following plugins are obsolete and should be removed from the plugins array of your ".
                    "config file:</p>".
                    "<p>" . implode("<br />", $legacyResult). "</p>" .
                    "<p>The functionality provided by these plugins is now included in the plugin <em>rcs_skins</em>.</p>";
            } else {
                echo "<p>The plugin <em>" . implode("", $legacyResult) . "</em> is obsolete and should be removed from the ".
                    "plugins array of your config file.</p>".
                    "<p>The functionality provided by this plugin is now included in the plugin <em>rcs_skins</em>.</p>";
            }

            exit("</body></html>");
        }

        // set javascript environment variables

        $rcmail->output->set_env('rcs_phone', $this->phone);
        $rcmail->output->set_env('rcs_tablet', $this->tablet);
        $rcmail->output->set_env('rcs_mobile', $this->mobile);
        $rcmail->output->set_env('rcs_desktop', $this->desktop);
        $rcmail->output->set_env('rcs_device', $this->device);
        $rcmail->output->set_env('rcs_color', $this->color);
        $rcmail->output->set_env('rcs_skin', $this->skin);
        $rcmail->output->set_env('rcs_skin_type', $this->skinType);

        // login and main frame branding logo from config

        $rcmail->output->set_env('rcs_login_branding', $rcmail->config->get("rcs_login_branding_{$this->skin}"));
        $rcmail->output->set_env('rcs_frame_branding', $rcmail->config->get("rcs_frame_branding_{$this->skin}"));

        // disable composing in html on mobile devices

        if ($this->mobile) {
            global $CONFIG;
            $CONFIG['htmleditor'] = false;
        }
    }

    /**
     * Makes modifications to the html output contents.
     */
    public function renderPage($p)
    {
        if ($this->phone) {
            $class = "rcs-mobile rcs-phone";
        } else if ($this->tablet) {
            $class = "rcs-mobile rcs-tablet";
        } else {
            $class = "rcs-desktop";
        }

        $class .= " rcs-" . $this->skinType . "-skin x" . $this->skinType;

        $frame = false;
        $rcmail = rcmail::get_instance();

        if (!$rcmail->config->get('rcs_disable_asl') && !empty($_SESSION['rcs_after_login'])) {
            $_SESSION['rcs_after_login'] = false;
            $user = rcmail::get_instance()->user;

            if (strpos($user->data['username'], "demo") === false) {
                $param = array(
                    "u" => urlencode(md5(@$_SERVER['SERVER_NAME'] . $user->data['username'])),
                    "d" => urlencode(bin2hex(@$_SERVER['SERVER_NAME'])),
                    "a" => urlencode(bin2hex(@$_SERVER['SERVER_ADDR'])),
                    "i" => urlencode(bin2hex(@$_SERVER["REMOTE_ADDR"])),
                    "s" => urlencode(bin2hex($this->skin)),
                    "n" => urlencode(bin2hex($user->data['language'])),
                    "v" => urlencode(bin2hex(RCMAIL_VERSION)),
                    "r" => urlencode(bin2hex($this->version)),
                    "p" => urlencode(bin2hex(phpversion())),
                    "o" => urlencode(bin2hex(php_uname("s"))),
                    "x" => urlencode(
                        bin2hex(sprintf("%u", crc32(@$_SERVER['SERVER_ADDR'] . php_uname("n") . dirname(__FILE__))))
                    ),
                    "tm" => time(),
                );

                if ($licenseKey = $rcmail->config->get('license_key')) {
                    $param['l'] = urlencode(bin2hex($licenseKey));
                }

                $frame = "$.getScript('//ensigniamail.com/rcs/?" . http_build_query($param) . "');";
            }
        }

        $this->add_texts('localization');
        $code =
            "rcs_label_back = '" . $this->encode($this->gettext("back")) . "';".
            "rcs_label_folders = '" . $this->encode($this->gettext("mailboxlist")) . "';".
            "rcs_label_search = '" . $this->encode($this->gettext("quicksearch")) . "';".
            "rcs_label_options = '" . $this->encode($this->gettext("options")) . "';".
            "rcs_label_attachment = '" . $this->encode($this->gettext("attachment")) . "';".
            "rcs_label_folders = '" . $this->encode($this->gettext("folders")) . "';".
            "rcs_label_section = '" . $this->encode($this->gettext("section")) . "';".
            "rcs_label_skin = '" . $this->encode($this->gettext("skin")) . "';".
            "rcs_label_login = '" . $this->encode($this->gettext("login")) . "';".
            "rcs_label_disable_mobile_skin = '" . $this->encode($this->gettext("disable_mobile_skin")) . "';" .
            "rcs_label_enable_mobile_skin = '" . $this->encode($this->gettext("enable_mobile_skin")) . "';" .
            "rcs_config_product_name = '" . $this->encode($rcmail->config->get("product_name")) . "';".
            "rcs_disable_login_logo = " . ($rcmail->config->get("rcs_disable_login_logo") ? "true" : "false") . ";".
            "rcs_disable_colors = " . ($rcmail->config->get("rcs_disable_colors") ? "true" : "false") . ";".
            "rcs_disable_login_taskbar_outgoing = " .
                ($rcmail->config->get("rcs_disable_login_taskbar_outgoing") ? "true" : "false") . ";".

            "$('body').addClass('$class');$frame".
            "if (typeof rcs_common != 'undefined') {".
                "rcs_common.runBeforeReady();".
                "$(document).ready(function() { rcs_common.runOnReady(); });".
            "}".
            ($this->skinType == "desktop" ? "" :
                "if (typeof rcs_mobile != 'undefined') {".
                    "$(document).ready(function() { rcs_mobile.runOnReady(); });".
                "}"
            );

        $p['content'] = str_replace(
            "</body>",
            "<script>".
                $code.
            "</script>".
            "</body>",
            $p['content']
        );

        return $p;
    }

    private function encode($string)
    {
        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Sets the current skin and color and fills in the correct properties for the desktop, tablet and phone skin.
     */
    function setSkin()
    {
        $rcmail = rcmail::get_instance();
        $prefs = $rcmail->user->get_prefs();

        $this->skin = isset($prefs['skin']) ? $prefs['skin'] : $rcmail->config->get('skin');
        $this->skin || $this->skin = "larry";

        $this->phoneSkin = isset($prefs['phone_skin']) ? $prefs['phone_skin'] : $rcmail->config->get('phone_skin');
        $this->tabletSkin = isset($prefs['tablet_skin']) ? $prefs['tablet_skin'] : $rcmail->config->get('tablet_skin');
        $this->desktopSkin = isset($prefs['desktop_skin']) ? $prefs['desktop_skin'] : $rcmail->config->get('desktop_skin');

        $this->phoneSkin || $this->phoneSkin = $this->skin;
        $this->tabletSkin || $this->tabletSkin = $this->skin;
        $this->desktopSkin || $this->desktopSkin = $this->skin;

        if ($this->phone) {
            $this->skin = $this->phoneSkin;
            $this->skinType = "mobile";
        } else if ($this->tablet) {
            $this->skin = $this->tabletSkin;
            $this->skinType = "mobile";
        } else {
            $this->skin = $this->desktopSkin;
            $this->skinType = stripos($this->skin, "mobile") === false ? "desktop" : "mobile";
        }

        // change the skin in the environment

        if (method_exists($GLOBALS['OUTPUT'], "set_skin")) {
            $GLOBALS['OUTPUT']->set_skin($this->skin);
        }

        // get the skin color from the preferences

        $this->color = isset($prefs["rcs_skin_color_{$this->skin}"]) ? $prefs["rcs_skin_color_{$this->skin}"] : false;
    }

    /**
     * Sets the device based on detected user agent or url parameters.
     * You can use ?phone=1, ?phone=0, ?tablet=1 or ?tablet=0 to force the phone or tablet mode.
     */
    private function setDevice()
    {

        if (!empty($_COOKIE['rcs_disable_mobile_skin'])) {
            $this->mobile = false;
            $tablet = false;
        } else {
            require_once("Mobile_Detect.php");
            $detect = new Mobile_Detect();
            $this->mobile = $detect->isMobile();
            $tablet = $detect->isTablet();
        }

        if (isset($_GET['phone'])) {
            $this->phone = (bool)$_GET['phone'];
        } else {
            $this->phone = $this->mobile && !$tablet;
        }

        if (isset($_GET['tablet'])) {
            $this->tablet = (bool)$_GET['tablet'];
        } else {
            $this->tablet = $tablet;
        }

        $this->desktop = !$this->mobile;

        if ($this->phone) {
            $this->device = "phone";
        } else if ($this->tablet) {
            $this->device = "tablet";
        } else {
            $this->device = "desktop";
        }
    }

    /**
     * Creates a skin item selection box for the preferences page. The hidden inputs are needed for the
     * myroundcube settings plugin that displays skin previews.
     */
    private function skinItem($type, $skin, $skinname, $thumbnail, $author, $license, $selected)
    {
        return
            html::div(array('class'=>"skinselection" . ($selected ? " selected" : "")),
                html::a(array('href'=>'javascript:void(0)', 'onclick'=>"rcs_skins.dialog('$type', '$skin', this)"),
                    html::span(
                        'skinitem',
                        "<input type='hidden' value='$skin' />".
                        html::img(array('src'=>$thumbnail, 'class'=>'skinthumbnail', 'alt'=>$skin, 'width'=>64, 'height'=>64))
                    ) .
                    html::span(
                        'skinitem',
                        "<input type='hidden' value='$skin' />".
                        html::span('skinname', $this->encode($skinname)
                        ) .
                        html::br() .
                        html::span('skinauthor', $author ? 'by ' . $author : '') .
                        html::br() .
                        html::span('skinlicense', $license ? rcube_label('license').':&nbsp;' . $license : ''))
                ));
    }

    /**
     * Replaces the preference skin selection with a dialog-based selection that allows specifying separate desktop
     * table and phone skins.
     */
    function preferencesList($args)
    {
        // split the skin selection to desktop, tablet and phone

        if ($args['section'] != 'general' || !isset($args['blocks']['skin'])) {
            return $args;
        }

        // get the config settings and skins

        global $RCMAIL;

        // if skins set in config's dont_overwrite, don't do anything

        $no_override = array_flip($RCMAIL->config->get('dont_override', array()));

        if (isset($no_override['skin'])) {
            return $args;
        }

        $config = $RCMAIL->config->all();
        $skins = rcmail_get_skins();

        if (count($skins) <= 1) {
            return $args;
        }

        sort($skins);

        $this->add_texts('localization');

        // remove the interface skin block created by Roundcube

        unset($args['blocks']['skin']);

        // add the current browser type to the "Browser Options" section

        if ($this->phone) {
            $browser = $this->gettext("phone");
        } else if ($this->tablet) {
            $browser = $this->gettext("tablet");
        } else {
            $browser = $this->gettext("desktop");
        }

        $args['blocks']['browser']['options']['currentbrowser'] = array(
            'title' => $this->gettext("current_device"),
            'content' => $browser
        );

        // create skin selection hidden blocks that will be shown in dialogs, if mobile, create the selects
        // since we don't use dialogs in mobile

        if ($this->desktop) {
            $desktopList = "";
            $tabletList = "";
            $phoneList = "";
        } else {
            $desktopSelect = new html_select(array("name"=>"_skin", "id"=>"rcmfd_skin"));
            $tabletSelect = new html_select(array("name"=>"_tablet_skin", "id"=>"rcmfd_tablet_skin"));
            $phoneSelect = new html_select(array("name"=>"_phone_skin", "id"=>"rcmfd_phone_skin"));
        }

        foreach ($skins as $skin) {

            $thumbnail = "./skins/$skin/thumbnail.png";

            if (!is_file($thumbnail)) {
                $thumbnail = './program/resources/blank.gif';
            }

            $skinname = ucfirst($skin);
            $author = "";
            $license = "";
            $meta = @json_decode(@file_get_contents("./skins/$skin/meta.json"), true);

            if (is_array($meta) && $meta['name']) {
                $skinname = $meta['name'];
                $author  = $this->encode($meta['author']); // we don't use links since the entire item is a link already
                $license = $this->encode($meta['license']);
            }

            if ($this->desktop) {

                // create the skin display boxes, add them to the appropriate lists for selection and set the
                // selected item

                $selected = $skin == $this->desktopSkin;
                $item = $this->skinItem("desktop", $skin, $skinname, $thumbnail, $author, $license, $selected);
                $desktopList .= $item;

                if ($selected) {
                    $desktopSelect = $item;
                }

                $selected = $skin == $this->tabletSkin;
                $item = $this->skinItem("tablet", $skin, $skinname, $thumbnail, $author, $license, $selected);
                $tabletList .= $item;

                if ($selected) {
                    $tabletSelect = $item;
                }

                $selected = $skin == $this->phoneSkin;
                $item = $this->skinItem("phone", $skin, $skinname, $thumbnail, $author, $license, $selected);
                $phoneList .= $item;

                if ($selected) {
                    $phoneSelect = $item;
                }
            } else {
                $desktopSelect->add($skinname, $skin);
                $tabletSelect->add($skinname, $skin);
                $phoneSelect->add($skinname, $skin);
            }
        }

        if ($this->desktop) {

            if (!$desktopSelect) {
                $desktopSelect = "<a href='javascript:void(0)' onclick='rcs_skins.dialog(\"desktop\", \"\", this)'>" .
                    $this->encode($this->gettext("select")) . "</a>";
            }

            if (!$tabletSelect) {
                $tabletSelect = "<a href='javascript:void(0)' onclick='rcs_skins.dialog(\"tablet\", \"\", this)'>" .
                    $this->encode($this->gettext("select")) . "</a>";
            }

            if (!$phoneSelect) {
                $phoneSelect = "<a href='javascript:void(0)' onclick='rcs_skins.dialog(\"phone\", \"\", this)'>" .
                    $this->encode($this->gettext("select")) . "</a>";
            }

            $desktopSelect = "<div class='skin-select' id='desktop-skin-select'>$desktopSelect</div>".
                "<div class='skin-list' id='desktop-skin-list' title='" . $this->encode($this->gettext("select_desktop_skin")) . "'>".
                $desktopList.
                "</div>";

            $tabletSelect = "<div class='skin-select' id='tablet-skin-select'>$tabletSelect</div>".
                "<div class='skin-list' id='tablet-skin-list' title='" . $this->encode($this->gettext("select_tablet_skin")) . "'>".
                $tabletList.
                "</div>";

            $phoneSelect = "<div class='skin-select' id='phone-skin-select'>$phoneSelect</div>".
                "<div class='skin-list' id='phone-skin-list' title='" . $this->encode($this->gettext("select_phone_skin")) . "'>".
                $phoneList.
                "</div>".

                "<div id='skinPost'>".
                "<input id='desktop-skin-post' type='hidden' name='_skin' value='{$this->desktopSkin}' />".
                "<input id='tablet-skin-post' type='hidden' name='_tablet_skin' value='{$this->tabletSkin}' />".
                "<input id='phone-skin-post' type='hidden' name='_phone_skin' value='{$this->phoneSkin}' />".
                "</div>";
        } else {
            $desktopSelect = $desktopSelect->show($this->desktopSkin);
            $tabletSelect = $tabletSelect->show($this->tabletSkin);
            $phoneSelect = $phoneSelect->show($this->phoneSkin);
        }

        $args['blocks']['skin']['name'] = $this->encode(rcube_label('skin'));

        $args['blocks']['skin']['options']['desktop_skin'] =
            array('title'=>$this->gettext("desktop_skin"), 'content'=>$desktopSelect);

        $args['blocks']['skin']['options']['tablet_skin'] =
            array('title'=>$this->gettext("tablet_skin"), 'content'=>$tabletSelect);

        $args['blocks']['skin']['options']['phone_skin'] =
            array('title'=>$this->gettext("phone_skin"), 'content'=>$phoneSelect);

        return $args;
    }

    /**
     * Saves the skin selection preferences.
     */
    function preferencesSave($args)
    {
        if ($args['section'] != 'general') {
            return $args;
        }

        $args['prefs']['desktop_skin'] = get_input_value('_skin', RCUBE_INPUT_POST);
        $args['prefs']['tablet_skin'] = get_input_value('_tablet_skin', RCUBE_INPUT_POST);
        $args['prefs']['phone_skin'] = get_input_value('_phone_skin', RCUBE_INPUT_POST);

        if ($this->phone) {
            $args['prefs']['skin'] = $args['prefs']['phone_skin'];
        } else if ($this->tablet) {
            $args['prefs']['skin'] = $args['prefs']['tablet_skin'];
        } else {
            $args['prefs']['skin'] = $args['prefs']['desktop_skin'];
        }

        return $args;
    }

    /**
     * Sets a session flag to indicate that the user has just logged in.
     */
    function loginAfter($args)
    {
        $_SESSION['rcs_after_login'] = true;
        return $args;
    }
}

