createObjectHandler["url"] = "createURL";

function createURL(id) {

        parm = centerParms(400,350) + ",width=400,height=400,scrollbars=no,status=yes";
        url = "index.php?module=newurl&parentId=" + id;

        window.open(url,'_new',parm);

}
