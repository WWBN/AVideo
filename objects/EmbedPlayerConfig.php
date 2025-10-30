<?php

/**
 * Classe para gerenciar configurações do player embedado
 * Centraliza todos os parâmetros que podem ser passados via URL
 */
class EmbedPlayerConfig
{
    // Configurações do player
    private $modestbranding = false;
    private $autoplay = false;
    private $controls = 'controls';
    private $showOnlyBasicControls = false;
    private $hideProgressBarAndUnPause = false;
    private $loop = '';
    private $mute = '';
    private $objectFit = '';
    private $t = 0;

    // Configurações de aparência
    private $showBigButton = false;
    private $disableEmbedTopInfo = false;
    private $showInfo = true;

    // Configurações de comportamento
    private $forceCloseButton = false;
    private $closeOnEnd = false;
    private $disableShareButton = false;

    // Video data
    private $video = null;
    private $config = null;

    /**
     * Construtor - inicializa a partir de $_GET e configurações globais
     */
    public function __construct($video = null, $config = null)
    {
        $this->video = $video;
        $this->config = $config;
        $this->loadFromRequest();
    }

    /**
     * Carrega configurações da URL ($_GET)
     */
    private function loadFromRequest()
    {
        // Modestbranding
        if (isset($_GET['modestbranding']) && $_GET['modestbranding'] == "1") {
            $this->modestbranding = true;
        }

        // Autoplay
        if (!empty($_GET['autoplay']) || ($this->config && $this->config->getAutoplay())) {
            $this->autoplay = true;
        }

        // Controls
        if (isset($_GET['controls'])) {
            if ($_GET['controls'] == "0") {
                $this->controls = '';
            } elseif ($_GET['controls'] == "-1") {
                $this->showOnlyBasicControls = true;
            } elseif ($_GET['controls'] == "-2") {
                $this->showOnlyBasicControls = true;
                $this->hideProgressBarAndUnPause = true;
            }
        }

        // Loop
        if (!empty($_GET['loop'])) {
            $this->loop = "loop";
        }

        // Mute
        if (!empty($_GET['mute'])) {
            $this->mute = 'muted="muted"';
        }

        // Object Fit
        if (!empty($_GET['objectFit']) && (intval($_GET['objectFit']) == 1 || $_GET['objectFit'] == 'true')) {
            $this->objectFit = 'object-fit: ' . $_GET['objectFit'];
        }

        // Time (t)
        if (!empty($_GET['t'])) {
            $this->t = intval($_GET['t']);
        } elseif (!empty($this->video['progress']['lastVideoTime'])) {
            $this->t = intval($this->video['progress']['lastVideoTime']);
        } elseif (!empty($this->video['externalOptions']->videoStartSeconds)) {
            $this->t = parseDurationToSeconds($this->video['externalOptions']->videoStartSeconds);
        }

        // Show Big Button
        if (!empty($_REQUEST['showBigButton'])) {
            $this->showBigButton = true;
        }

        // Show Info
        if (isset($_REQUEST['showinfo']) && empty($_REQUEST['showinfo'])) {
            $this->showInfo = false;
            $this->modestbranding = true;
        }

        // Force Close Button
        if (!empty($_REQUEST['forceCloseButton'])) {
            $this->forceCloseButton = true;
        }

        // Close On End
        if (!empty($_REQUEST['closeOnEnd'])) {
            $this->closeOnEnd = true;
        }

        // Disable Share Button
        if (!empty($_REQUEST['disableShareButton'])) {
            $this->disableShareButton = true;
        }

        // Disable Embed Top Info (combinado com PlayerSkins)
        if ($this->config) {
            $playerSkinsO = AVideoPlugin::getObjectData("PlayerSkins");
            $this->disableEmbedTopInfo = $playerSkinsO->disableEmbedTopInfo;

            if (!$this->showInfo) {
                $this->disableEmbedTopInfo = true;
            }
        }
    }

    /**
     * Define configurações programaticamente
     */
    public function set($key, $value)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * Obtém configuração específica
     */
    public function get($key)
    {
        return property_exists($this, $key) ? $this->$key : null;
    }

    // Getters específicos
    public function isModestbranding() { return $this->modestbranding; }
    public function isAutoplay() { return $this->autoplay; }
    public function getControls() { return $this->controls; }
    public function showOnlyBasicControls() { return $this->showOnlyBasicControls; }
    public function hideProgressBarAndUnPause() { return $this->hideProgressBarAndUnPause; }
    public function getLoop() { return $this->loop; }
    public function getMute() { return $this->mute; }
    public function getObjectFit() { return $this->objectFit; }
    public function getStartTime() { return $this->t; }
    public function showBigButton() { return $this->showBigButton; }
    public function isEmbedTopInfoDisabled() { return $this->disableEmbedTopInfo; }
    public function showInfo() { return $this->showInfo; }
    public function forceCloseButton() { return $this->forceCloseButton; }
    public function closeOnEnd() { return $this->closeOnEnd; }
    public function isShareButtonDisabled() { return $this->disableShareButton; }

    /**
     * Retorna a classe CSS do VJS
     */
    public function getVjsClass()
    {
        $class = '';
        if ($this->showBigButton) {
            $class .= ' showBigButton';
        }
        return $class;
    }

    /**
     * Retorna atributos HTML para a tag <video>
     */
    public function getVideoAttributes()
    {
        $attrs = [];

        if ($this->controls) {
            $attrs[] = $this->controls;
        }

        if ($this->loop) {
            $attrs[] = $this->loop;
        }

        if ($this->mute) {
            $attrs[] = $this->mute;
        }

        return implode(' ', $attrs);
    }

    /**
     * Retorna o estilo inline para object-fit
     */
    public function getVideoStyle()
    {
        $style = "width: 100%; height: 100%; position: fixed; top: 0; left:0;";
        if ($this->objectFit) {
            $style .= ' ' . $this->objectFit . ';';
        }
        return $style;
    }

    /**
     * Retorna todos os metadados dos campos em um único array
     * Centralized configuration for all embed parameters
     */
    public static function getFieldsMetadata()
    {
        return [
            'modestbranding' => [
                'name' => 'Modest Branding',
                'type' => 'checkbox',
                'description' => 'Remove AVideo branding from player',
            ],
            'autoplay' => [
                'name' => 'Autoplay',
                'type' => 'checkbox',
                'description' => 'Start playing video automatically',
            ],
            'controls' => [
                'name' => 'Controls',
                'type' => 'hidden',
                'description' => 'Controlled by special group',
            ],
            'showOnlyBasicControls' => [
                'name' => 'Basic Controls Only',
                'type' => 'radio-controls',
                'description' => 'Shows only essential controls: play/pause, volume, progress bar (seekable), resolution selector, and fullscreen button. Hides all other advanced controls.',
            ],
            'hideProgressBarAndUnPause' => [
                'name' => 'Hide Progress Bar & Prevent Pause',
                'type' => 'radio-controls',
                'description' => 'Restrictive mode: hides progress bar and play/pause button. Automatically resumes if user tries to pause. Shows only volume, fullscreen, and resolution. Useful for mandatory viewing where users cannot pause or skip ahead.',
            ],
            'loop' => [
                'name' => 'Loop',
                'type' => 'checkbox',
                'description' => 'Loop video playback',
            ],
            'mute' => [
                'name' => 'Mute',
                'type' => 'checkbox',
                'description' => 'Mute video audio',
            ],
            'objectFit' => [
                'name' => 'Object Fit',
                'type' => 'select',
                'description' => 'Controls how the video is resized to fit its container. Cover: fills container (may crop), Contain: fits entirely (may show borders), Fill: stretches to fill, None: original size, Scale-down: smaller of none or contain',
                'options' => [
                    '' => 'Default',
                    'cover' => 'Cover',
                    'contain' => 'Contain',
                    'fill' => 'Fill',
                    'none' => 'None',
                    'scale-down' => 'Scale Down',
                ],
            ],
            't' => [
                'name' => 'Start Time (seconds)',
                'type' => 'number',
                'description' => 'Start video at specific time in seconds',
            ],
            'showBigButton' => [
                'name' => 'Show Big Play Button',
                'type' => 'checkbox',
                'description' => 'Display large play button overlay',
            ],
            'disableEmbedTopInfo' => [
                'name' => 'Disable Embed Top Info',
                'type' => 'hidden',
                'description' => 'Internal only',
            ],
            'showInfo' => [
                'name' => 'Show Info',
                'type' => 'checkbox-inverted',
                'description' => 'Hide video title and information',
            ],
            'forceCloseButton' => [
                'name' => 'Force Close Button',
                'type' => 'checkbox',
                'description' => 'Show close button in player',
            ],
            'closeOnEnd' => [
                'name' => 'Close on End',
                'type' => 'checkbox',
                'description' => 'Automatically close player when video ends',
            ],
            'disableShareButton' => [
                'name' => 'Disable Share Button',
                'type' => 'checkbox',
                'description' => 'Hide share button from player controls',
            ],
        ];
    }

    /**
     * Retorna os nomes amigáveis para cada parâmetro
     */
    public static function getFriendlyNames()
    {
        $metadata = self::getFieldsMetadata();
        $names = [];
        foreach ($metadata as $key => $data) {
            $names[$key] = $data['name'];
        }
        return $names;
    }

    /**
     * Retorna os tipos de campos para cada parâmetro
     */
    public static function getFieldTypes()
    {
        $metadata = self::getFieldsMetadata();
        $types = [];
        foreach ($metadata as $key => $data) {
            $types[$key] = $data['type'];
        }
        return $types;
    }

    /**
     * Retorna as opções para campos select
     */
    public static function getSelectOptions($fieldName)
    {
        $metadata = self::getFieldsMetadata();
        return isset($metadata[$fieldName]['options']) ? $metadata[$fieldName]['options'] : [];
    }

    /**
     * Retorna a descrição/ajuda para cada parâmetro
     */
    public static function getFieldDescriptions()
    {
        $metadata = self::getFieldsMetadata();
        $descriptions = [];
        foreach ($metadata as $key => $data) {
            if (isset($data['description'])) {
                $descriptions[$key] = $data['description'];
            }
        }
        return $descriptions;
    }

    /**
     * Retorna configurações como array (útil para debug/logs)
     */
    public function toArray()
    {
        return [
            'modestbranding' => $this->modestbranding,
            'autoplay' => $this->autoplay,
            'controls' => $this->controls,
            'showOnlyBasicControls' => $this->showOnlyBasicControls,
            'hideProgressBarAndUnPause' => $this->hideProgressBarAndUnPause,
            'loop' => $this->loop,
            'mute' => $this->mute,
            'objectFit' => $this->objectFit,
            't' => $this->t,
            'showBigButton' => $this->showBigButton,
            'disableEmbedTopInfo' => $this->disableEmbedTopInfo,
            'showInfo' => $this->showInfo,
            'forceCloseButton' => $this->forceCloseButton,
            'closeOnEnd' => $this->closeOnEnd,
            'disableShareButton' => $this->disableShareButton,
        ];
    }

    /**
     * Retorna as configurações em formato para embed (com nomes amigáveis)
     */
    public function toArrayWithFriendlyNames()
    {
        $data = $this->toArray();
        $friendlyNames = self::getFriendlyNames();
        $result = [];

        foreach ($data as $key => $value) {
            $result[$key] = [
                'value' => $value,
                'friendlyName' => isset($friendlyNames[$key]) ? $friendlyNames[$key] : $key,
                'type' => self::getFieldTypes()[$key] ?? 'text',
            ];
        }

        return $result;
    }

    /**
     * Retorna configurações como JSON
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Valida se as configurações são válidas
     */
    public function validate()
    {
        $errors = [];

        // Validação do controls
        if (!in_array($this->controls, ['controls', ''])) {
            $errors[] = "Invalid controls value";
        }

        // Validação do objectFit
        if ($this->objectFit && !preg_match('/^object-fit:\s*(cover|contain|fill|none|scale-down)$/', $this->objectFit)) {
            $errors[] = "Invalid objectFit value";
        }

        // Validação do tempo
        if ($this->t < 0) {
            $errors[] = "Start time cannot be negative";
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Gera CSS dinâmico baseado nas configurações
     */
    public function getCustomCSS()
    {
        $css = [];

        if ($this->disableShareButton) {
            $css[] = ".social-button { display: none !important; }";
        }

        // Hide the bootstrapMenu dropdown if either loop button or share button is hidden
        if ($this->disableShareButton || !empty($this->loop)) {
            $css[] = ".dropdown.bootstrapMenu { display: none !important; }";
        }

        if (empty($this->controls)) {
            $css[] = "#topInfo, .vjs-big-play-button, .vjs-control-bar, #seekBG { display: none !important; }";
        } elseif ($this->showOnlyBasicControls) {
            $css[] = "#mainVideo>div.vjs-control-bar>.vjs-control, #mainVideo>div.vjs-control-bar>div.vjs-time-divider { display: none; }";
            $css[] = "#mainVideo>div.vjs-control-bar>.vjs-play-control, #mainVideo>div.vjs-control-bar>.vjs-fullscreen-control { display: inline-block; }";
            $css[] = "#mainVideo>div.vjs-control-bar>.vjs-volume-panel, #mainVideo>div.vjs-control-bar>.vjs-progress-control, #mainVideo>div.vjs-control-bar>.vjs-resolution-button { display: flex; }";

            if ($this->hideProgressBarAndUnPause) {
                $css[] = "#mainVideo>div.vjs-control-bar>.vjs-progress-control, #mainVideo>div.vjs-control-bar>button.vjs-play-control { display: none; }";
            }
        }

        $css[] = "#mainVideo>div.vjs-control-bar { bottom: 0 !important; }";

        return implode("\n", $css);
    }

    /**
     * Gera JavaScript dinâmico baseado nas configurações
     */
    public function getCustomJS()
    {
        $js = [];

        if ($this->forceCloseButton) {
            $js[] = "addCloseButtonInVideo(true);";
        }

        if ($this->closeOnEnd) {
            $js[] = "player.on('ended', function() { $('#CloseButtonInVideo').trigger('click'); });";
        }

        if ($this->hideProgressBarAndUnPause) {
            $js[] = "player.on('pause', function() { player.play(); });";
        }

        return implode("\n", $js);
    }
}
