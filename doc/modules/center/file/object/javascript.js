createObjectHandler["file"] = "uploadFile";

function uploadFile(id) {

        parm = centerParms(600,475,1) + ",scrollbars=no,status=yes";
        url = "index.php?module=upload&parentId=" + id;

        window.open(url,'_new',parm);

}
