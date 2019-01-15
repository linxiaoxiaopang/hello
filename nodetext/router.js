var express = require('express')
var router = express.Router()
var fs = require('fs')
var Student = require('./student')

// 首页
router.get('/student',function(req, res){
	Student.find(function(err,data){
		if(err){
			return res.status(500).send('Server error.')
		}
		res.render('student.html', {
			frults: ['香蕉', '苹果', '梨子'],
			comments: data
		})

	})
})

// 渲染添加学生界面
router.get('/student/new',function(req, res){
	res.render('student/new.html')
})
 
// 处理添加学生请求
router.post('/student/new',function(req, res){
	Student.save(req.body, function(err, data){
		if(err){
			return res.status(500).send('Server error.')
		}
		res.redirect('/student')
	})

})

// 渲染需要编辑的页面
router.get('/student/redit',function(req, res){
	Student.findById(req.query.id, function(err, data){
		if(err){
			return res.status(500).send('Server error.')
		}
		res.render('student/redit.html', {
			comments: data
		})
	})
})

// 提交编辑页面信息
router.post('/student/redit',function(req, res){
	Student.reditById(req.body, function(err, data){
		if(err){
			return res.status(500).send('Server error.')
		}
		res.redirect('/student')

	})
	
})

router.get('/student/delete',function(req, res){
	Student.deleteById(req.query.id, function(err, data){
		if(err){
			return res.status(500).send('Server error.')
		}
		res.redirect('/student')
		
	})
})

module.exports=router