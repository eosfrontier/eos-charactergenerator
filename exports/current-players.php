<?php
$EVENTID = '5'
//$EVENTIDS = '(1,36,37,38,39,40,41,42,43,45,46,47,48,49,50,51,52,53,54,55,56,58,59,61,62,64,65,66,67,68,69,71,72,73,74,75,78,79,80,81,82,83,84,86,87,88,90,93,97,98,99,100,102,103,104,105,106,107,108,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,135,136,137,139,140,141,143,144,145,147,154,155,164,168,170,176,179,183,186,192,195,197,200,203,204,206,209,211,215,231,234)';
$sql = "select r.id, SUBSTRING_INDEX(v1.field_value,' - ',1) as name, SUBSTRING_INDEX(SUBSTRING_INDEX(v1.field_value,' - ',2),' - ',-1) as CharID from joomla.jml_eb_registrants r
join joomla.jml_eb_field_values v1 on (v1.registrant_id = r.id and v1.field_id = 21)
join joomla.jml_eb_field_values v2 on (v2.registrant_id = r.id and v2.field_id = 36)
left join joomla.jml_eb_field_values v3 on (v3.registrant_id = r.id and v3.field_id = 37)
left join joomla.jml_eb_field_values v4 on (v4.registrant_id = r.id and v4.field_id = 38)
where r.event_id = " $EVENTID  "and ((r.published = 1 AND r.payment_method = 'os_ideal') OR (r.published in (0,1) AND r.payment_method = 'os_offline')) ORDER by name asc;";
$result = $UPLINK->query($sql);
$EVENTIDS = $result['CharID'];