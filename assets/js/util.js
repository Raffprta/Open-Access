    function mktoggle(tclass, v)
    {
        return '<i class="'+tclass+' fa fa-toggle-'+(v ? 'on' : 'off')+'"></i>'
    }

    function toggle(x)
    {
        if (x.hasClass('fa-toggle-off'))
	{
	   x.removeClass('fa-toggle-off');
	   x.addClass('fa-toggle-on')
	}
	else
	{
	   x.removeClass('fa-toggle-on');
	   x.addClass('fa-toggle-off')
	}
    }

    function dotoggle(e, x, bean, fld)
    {
	e.preventDefault();
	e.stopPropagation();
	var tr = $(x).parent().parent()
	$.post(base+'/ajax.php', {
	    op : 'toggle',
	    field : fld,
	    bean : bean,
	    id : tr.data('id')
	}, function(data){
	   toggle(x)
	})
    }

    function dodelbean(e, x, bean)
    {
	e.preventDefault();
	e.stopPropagation();
	bootbox.confirm('Are you sure you you want to delete this '+bean+'?', function(r){
	    if (r)
	    { // user picked OK
		var tr = $(x).parent().parent()
		$.post(base+'/ajax.php', {
			op :'delbean',
			'bean' : bean,
			id : tr.data('id')
		    },
		    function(data){
			tr.css('background-color', 'yellow').fadeOut(1500, function(){ tr.remove() })
		    }
		)
	    }
	})
    }
