### stored procedure for retrieval of site root pid for MySQL < 8.0
DELIMITER $$

DROP FUNCTION IF EXISTS `GetRootPageUid` $$
CREATE FUNCTION `GetRootPageUid` (GivenID INT) RETURNS INT
    DETERMINISTIC
BEGIN
    DECLARE parentUid, targetUid, isSiteRoot INT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE cur CURSOR FOR
        SELECT pid, is_siteroot FROM pages WHERE uid = targetUid;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET targetUid = GivenID;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO parentUid, isSiteRoot;
        IF done THEN
            LEAVE read_loop;
        END IF;

        IF isSiteRoot THEN
            CLOSE cur;
            RETURN targetUid;
        ELSE
            SET targetUid = parentUid;
        END IF;
    END LOOP;

    CLOSE cur;
    RETURN 0;
END $$

DELIMITER ;
