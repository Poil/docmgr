createObjectHandler["collection"] = "createCollection";

function createCollection(id) {

        parm = centerParms(400,350) + ",width=400,height=350,scrollbars=no,status=yes";
        url = "index.php?module=newcollection&parentId=" + id;

        window.open(url,'_new',parm);

}
