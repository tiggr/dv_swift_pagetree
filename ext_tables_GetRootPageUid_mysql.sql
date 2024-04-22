### stored procedure for retrieval of site root pid for MySQL
DELIMITER $$

DROP FUNCTION IF EXISTS `GetRootPageUid` $$
CREATE FUNCTION `GetRootPageUid` (GivenID INT) RETURNS INT
    DETERMINISTIC
BEGIN
    DECLARE parentUid, targetUid, isSiteRoot INT;
    SET targetUid = GivenID;

    WHILE TRUE DO
            IF targetUid = 0 THEN
                RETURN 0;
            END IF;

            SELECT  pid, is_siteroot INTO parentUid, isSiteRoot
            FROM pages WHERE uid =	targetUid;

            IF isSiteRoot THEN
                RETURN targetUid;
            ELSE
                SET targetUid = parentUid;
            END IF;
        END WHILE;
END $$
