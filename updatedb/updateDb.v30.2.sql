SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- Speeds up category-child lookups used by Category::getAllCategories()
-- and Category::getTotalCategories().
SET @category_parent_index_exists = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'categories'
      AND index_name = 'category_parent_idx'
);

SET @category_parent_index_sql = IF(
    @category_parent_index_exists = 0,
    'ALTER TABLE `categories` ADD INDEX `category_parent_idx` (`parentId` ASC)',
    'SELECT 1'
);

PREPARE category_parent_index_stmt FROM @category_parent_index_sql;
EXECUTE category_parent_index_stmt;
DEALLOCATE PREPARE category_parent_index_stmt;

UPDATE configurations SET version = '30.2', modified = now() WHERE id = 1;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
