-- Drop the first key
ALTER TABLE `vast_campaigns_has_videos`
DROP FOREIGN KEY `fk_vast_campaigns_has_videos_videos1`;

-- Add the new first key
ALTER TABLE `vast_campaigns_has_videos`
ADD CONSTRAINT `fk_vast_campaigns_has_videos_videos1_new`
FOREIGN KEY (`videos_id`)
REFERENCES `videos` (`id`)
ON DELETE CASCADE
ON UPDATE NO ACTION;

-- Drop the second key
ALTER TABLE `vast_campaigns_has_videos`
DROP FOREIGN KEY `fk_vast_campaigns_has_videos_vast_campaigns1`;

-- Add the new second key
ALTER TABLE `vast_campaigns_has_videos`
ADD CONSTRAINT `fk_vast_campaigns_has_videos_vast_campaigns1_new`
FOREIGN KEY (`vast_campaigns_id`)
REFERENCES `vast_campaigns` (`id`)
ON DELETE CASCADE
ON UPDATE NO ACTION;

ALTER TABLE `vast_campaigns_logs`
DROP FOREIGN KEY `fk_vast_campaigns_logs_vast_campaigns_has_videos1`,
ADD CONSTRAINT `fk_vast_campaigns_logs_vast_campaigns_has_videos1_cascade`
FOREIGN KEY (`vast_campaigns_has_videos_id`)
REFERENCES `vast_campaigns_has_videos` (`id`)
ON DELETE CASCADE
ON UPDATE NO ACTION;