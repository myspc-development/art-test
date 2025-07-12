<?php
use ArtPulse\AI\AutoTagger;
use ArtPulse\AI\BioSummaryRestController;

if (!defined('ABSPATH')) {
    exit;
}

AutoTagger::register();
BioSummaryRestController::register();
