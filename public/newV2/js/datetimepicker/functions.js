function configDateTimePicker() {
    const locale = (typeof _lang !== 'undefined' && _lang) ? _lang : 'pt-br';
    $("[data-picker='datetime']").datetimepicker({
        locale: locale,
        widgetPositioning: {
            vertical: 'bottom',
            horizontal: 'left'
        }
    });

    var currentDate = moment();
    $("[data-picker='calendar']").each(function (indexInput) {
        var tiposPerms = [];
        tiposPerms[0] = "de ontem para tras";
        tiposPerms[1] = "de hoje para tras";

        var perm = $(this).data('permitteds');
        var diasNegrito = $(this).data('days_bold');
        var permitirTodas = perm === undefined;
        var hasMaxDate = null;
        switch (perm.toString().toLowerCase()) {
            case tiposPerms[0]:
                hasMaxDate = tiposPerms[0];
                perm = null;
                permitirTodas = true;
                break;
            case tiposPerms[1]:
                hasMaxDate = tiposPerms[1];
                perm = null;
                permitirTodas = true;
                break;
        }

        if(is_empty(perm, 1)) perm = [];
        else perm = perm.split(",");

        var optsDateTime = {
            locale: locale, //DEFINIDO NO SISTEMA DE TRADUÇÃO
            inline: true,
            format: 'DD/MM/YYYY',
            useCurrent: true
        };
        if(!permitirTodas) optsDateTime['enabledDates'] = perm;
        if(!is_empty(diasNegrito, 1)) {
            diasNegrito = diasNegrito.split(",");
            //// diasNegrito = ['2018-07-29'];
            // optsDateTime['beforeShowDay'] = function (date) {
            //     var newClass =
            //         (
            //             $.inArray(
            //                 (
            //                     date.getMonth() + '-' +
            //                     date.getDate()+ '-' +
            //                     date.getFullYear()
            //                 ),
            //                 diasNegrito
            //             ) >= 0
            //         ) ? 'negrito' : '';
            //
            //     return {
            //         classes: newClass
            //     };
            // }
        }

        $(this).datetimepicker(optsDateTime);
        if(is_empty(perm) && !permitirTodas){
            $(this).data("DateTimePicker")
                .minDate(currentDate)
                .maxDate(currentDate)
                .disabledDates([currentDate]);
        }

        switch (hasMaxDate) {
            case tiposPerms[0]:
                $(this).data("DateTimePicker")
                    .maxDate(currentDate)
                    .disabledDates([currentDate]);
                break;
            case tiposPerms[1]:
                $(this).data("DateTimePicker")
                    .maxDate(currentDate);
                break;
        }
    });
    currentDate = null;

    $("[data-picker='date']").datetimepicker({
        locale: locale,
        format: 'DD/MM/YYYY',
        useCurrent: false,
        widgetPositioning: {
            vertical: 'bottom',
            horizontal: 'left'
        }
    });

    $("[data-picker='date-top-left']").datetimepicker({
        locale: locale,
        format: 'DD/MM/YYYY',
        useCurrent: false,
        widgetPositioning: {
            vertical: 'top',
            horizontal: 'left'
        }
    });

	$("[data-picker='date-top-left-custom-local']").datetimepicker({
		locale: locale,
		format: (typeof configLocation !== 'undefined' && configLocation.formatDatePicker)
			? configLocation.formatDatePicker
			: 'DD/MM/YYYY',
		useCurrent: false,
		widgetPositioning: {
			vertical: 'top',
			horizontal: 'left'
		}
	});

    $("[data-picker='date-right-now']").datetimepicker({
        locale: locale,
        format: 'DD/MM/YYYY',
        useCurrent: true,
        widgetPositioning: {
            vertical: 'bottom',
            horizontal: 'right'
        }
    });

    $("[data-picker='date-block']").datetimepicker({
        locale: locale,
        format: 'DD/MM/YYYY',
        minDate: new Date(),
        widgetPositioning: {
            vertical: 'bottom',
            horizontal: 'left'
        }
    });

    $("[data-picker='date-bottom-left']").datetimepicker({
        locale: locale,
        format: 'DD/MM/YYYY',
        useCurrent: false,
        widgetPositioning: {
            vertical: 'bottom',
            horizontal: 'left'
        }
    });

    $("[data-picker='date-inline']").datetimepicker({
        locale: locale,
        format: 'DD/MM/YYYY',
        useCurrent: false,
        inline: true
    });

    $("[data-picker='date-min']").datetimepicker({
        locale: locale,
        viewMode: 'years',
        format: 'MM/YYYY',
        useCurrent: false,
        widgetPositioning: {
            vertical: 'bottom',
            horizontal: 'left'
        }
    });
    $("[data-picker='date-min-right']").datetimepicker({
        locale: locale,
        viewMode: 'years',
        format: 'MM/YYYY',
        useCurrent: false,
        widgetPositioning: {
            vertical: 'bottom',
            horizontal: 'right'
        }
    });
    $("[data-picker='date-min-right-now']").datetimepicker({
        locale: locale,
        viewMode: 'years',
        format: 'MM/YYYY',
        useCurrent: true,
        widgetPositioning: {
            vertical: 'bottom',
            horizontal: 'right'
        }
    });

    $("[data-picker='time']").datetimepicker({
        locale: locale,
        format: 'HH:mm',
        useCurrent: false,
        widgetPositioning: {
            vertical: 'bottom',
            horizontal: 'left'
        }
    });
	$("[data-picker='date-year']").datetimepicker({
		locale: locale,
		viewMode: 'years',
		format: 'YYYY',
		useCurrent: false,
		widgetPositioning: {
			vertical: 'bottom',
			horizontal: 'left'
		}
	});
}
