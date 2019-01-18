$(function(){
	var mapId = location.href.split('?')[1].split('=')[1] ? location.href.split('?')[1].split('=')[1] : 1
	$('.mapTitle').html(mapId + '号大棚')
	var vegsData = null

	/**
	*初始化野狗数据库节点
	**/
	var config = {
		  syncURL: "https://wd0963251028wmtevt.wilddogio.com/vegData" //输入节点 URL
		}
		wilddog.initializeApp(config)
		var ref = wilddog.sync().ref()
	/* 
	*snapshot 里面的数据会一直和云端保持同步
	*/
	ref.on("value", function(snapshot) {
	    console.log(snapshot.val())
	    vegsData = snapshot.val() //获取野狗数据库数据
	    var fistLineData = snapshot.val()[mapId].firstLine
	    var fistHtmlStr = template('firstLineTpl', {
	    	data : fistLineData
	    })

	    $('#firstLine').html(fistHtmlStr)

	    var roadLineData = snapshot.val()[mapId].roadLine
	    var roadHtmlStr = template('roadLineTpl', {
	    	data : roadLineData
	    })

	    $('#roadLine').html(roadHtmlStr)

	    var secondLineData = snapshot.val()[mapId].secondLine
	    var secondHtmlStr = template('secondLineTpl', {
	    	data : secondLineData
	    })

	    $('#secondLine').html(secondHtmlStr)
	})

	/*
	*给每个蔬菜所在的“a”标签，绑定点击事件
	*/
	$('.map').on('click', 'a', function(){
		var vegId = $(this).parent('div').attr('data-id')
		var vegIndex = $(this).attr('data-index')
		var vegStr ='id=' + mapId +'&'+ 'vegId=' + vegId + '&' + 'vegIndex=' + vegIndex
		$(this).attr('href','./detail.html?' + vegStr )
	})
})