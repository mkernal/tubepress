<?php
class TubePressAdvancedOptions extends TubePressOptionsCategory {
    
    const debugEnabled = "debugging_enabled";
    const filter = "filter_racy";
	const randomThumbs = "randomize_thumbnails";
	const timeout = "timeout";
	const triggerWord = "keyword";

    public function __construct() {
        
        $this->setTitle("Advanced");
        $this->setOptions(array(
        
            TubePressAdvancedOptions::triggerWord => new TubePressOption(
                TubePressAdvancedOptions::triggerWord,
                "Trigger keyword",
                "The word you insert (in plaintext, between square brackets) into your posts to display your YouTube gallery.",
                new TubePressTextValue(
                    TubePressAdvancedOptions::triggerWord,
                    "tubepress"
                )
            ),
            
            TubePressAdvancedOptions::debugEnabled => new TubePressOption(
                TubePressAdvancedOptions::debugEnabled,
                "Enable debugging",
                "If checked, " .
                             "anyone will be able to view your debugging " .
                             "information. This is a rather small privacy " .
                             "risk. If you're not having problems with " .
                             "TubePress, or you're worried about revealing " .
                             "any details of your TubePress pages, feel free to " .
                             "disable the feature.",
                new TubePressBoolValue(
                    TubePressAdvancedOptions::debugEnabled,
                    true
                )
            ),
            
            TubePressAdvancedOptions::randomThumbs => new TubePressOption(
                TubePressAdvancedOptions::randomThumbs,
                "Randomize thumbnails",
                "Most videos come with several thumbnails. By selecting this option, each time someone views your gallery they will see the same videos with each video's thumbnail randomized",
                new TubePressBoolValue(
                    TubePressAdvancedOptions::randomThumbs,
                    true
                )
            ),
            
            TubePressAdvancedOptions::filter => new TubePressOption(
                TubePressAdvancedOptions::filter,
            	"Filter \"racy\" content",
                "Don't show videos that may not be suitable for minors.",
                new TubePressBoolValue(
                    TubePressAdvancedOptions::filter,
                    true
                )
            )
        ));
    }
}
?>
                  