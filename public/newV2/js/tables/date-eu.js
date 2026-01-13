jQuery.extend(jQuery.fn.dataTableExt.oSort, {
    "date-eu-pre": function (a) {
        a = $.trim($('<i>' + a + '</i>').text());
        var x;

        if (a !== '') {
            var frDatea = $.trim(a).split(' ');
            var frTimea = (undefined != frDatea[1]) ? frDatea[1].split(':') : [00, 00, 00];
            var frDatea2 = frDatea[0].split('/');
            if($.trim(a).length > 16) {
                x = (frDatea2[2] + frDatea2[1] + frDatea2[0] + frTimea[0] + frTimea[1] + frTimea[2]) * 1;
            } else{
                x = (frDatea2[2] + frDatea2[1] + frDatea2[0] + frTimea[0] + frTimea[1] + 00) * 1;
            }
        } else {
            x = Infinity;
        }

        return x;
    },
    "date-eu-asc": function (a, b) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
    "date-eu-desc": function (a, b) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
});