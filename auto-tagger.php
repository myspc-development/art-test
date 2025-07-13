<?php
use ArtPulse\AI\AutoTagger;
use ArtPulse\AI\BioSummaryRestController;
use ArtPulse\AI\AutoTaggerRestController;

if (!defined('ABSPATH')) {
    exit;
}

AutoTagger::register();
BioSummaryRestController::register();
AutoTaggerRestController::register();
