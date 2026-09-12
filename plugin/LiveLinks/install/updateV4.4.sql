-- Add the index required by category existence/count queries.
CREATE INDEX IF NOT EXISTS `fk_livelinks_categories1_idx`
    ON `LiveLinks` (`categories_id` ASC);
