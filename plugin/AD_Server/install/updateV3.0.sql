ALTER TABLE `vast_campaigns_has_videos`
DROP FOREIGN KEY `fk_vast_campaigns_has_videos_videos1`,
ADD CONSTRAINT `fk_vast_campaigns_has_videos_videos1`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
DROP FOREIGN KEY `fk_vast_campaigns_has_videos_vast_campaigns1`,
ADD CONSTRAINT `fk_vast_campaigns_has_videos_vast_campaigns1`
    FOREIGN KEY (`vast_campaigns_id`)
    REFERENCES `vast_campaigns` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION;
