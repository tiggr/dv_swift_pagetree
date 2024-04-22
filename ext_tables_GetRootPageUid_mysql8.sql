### stored procedure for retrieval of site root pid for MySQL >= 8.0
DELIMITER $$

DROP FUNCTION IF EXISTS `GetRootPageUid` $$
CREATE FUNCTION `GetRootPageUid` (GivenID INT) RETURNS INT
    DETERMINISTIC
BEGIN
    WITH RECURSIVE PageHierarchy AS (
        SELECT uid, pid, is_siteroot
        FROM pages
        WHERE uid = GivenID
        UNION ALL
        SELECT p.uid, p.pid, p.is_siteroot
        FROM pages p
                 INNER JOIN PageHierarchy ph ON ph.pid = p.uid
    )
    SELECT uid
    FROM PageHierarchy
    WHERE is_siteroot = 1
    ORDER BY (CASE WHEN uid = GivenID THEN 1 ELSE 0 END) DESC
    LIMIT 1;
END $$

DELIMITER ;
