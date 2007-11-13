createObjectHandler["document"] = "createDocument";

function createDocument(id) {

        parm = centerParms(450,350,1) + ",scrollbars=no,status=yes";
        url = "index.php?module=newdoc&parentId=" + id;

        window.open(url,'_new',parm);

}
