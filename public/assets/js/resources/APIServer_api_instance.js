$('.navbar-header .nav a h4').html('API Instance');
url = "/api/proxy/"+slug+"/api_instances/"+resource_id;
api = url;
myapi = {};
$.ajax({
	url: url,		
	success: function(api){
		myapi = api;
		api.server = server;
		$('#table').html(templates.api_instance.render(api));
		$.ajax({
			url: '/api/proxy/'+slug+'/apis/'+api.api_id+'/versions',		
			success: function(versions){

				versions.unshift({id:0,label:'Latest Published'})
				versions.unshift({id:-1,label:'Latest (Working or Published)'})
				new gform({
					name:'main',
					data:api,
					actions:[],
					fields:[
						{label: 'Name', name:'name', required: true},
						{label: 'Slug', name:'slug', required: true},
						{label: 'Environment', name:'environment_id', enabled: false,type:'select',options:'/api/proxy/'+slug+'/environments',format:{label:"{{name}}",value:"{{id}}"}},
						{label: 'API', name:'api_id',type:'select', enabled: false,options:'/api/proxy/'+slug+'/apis',format:{label:"{{name}}",value:"{{id}}"}},
						{label: 'API Version', name:'api_version_id', enabled: false,type:'select',options:versions,format:{value:"{{id}}"}},		
						{label: 'Error Level', name:"errors", options:[{label:"None",value:"none"},{label:"All",value:"all"}],type:"select"},	
					]},'.main')

				if(api.api_version.resources.length >0 && api.api_version.resources[0].name.length){
					var data = _.merge(api,api.api_version)
					new gform({
						name: 'resources',
						data: data,
						actions:[],
						
						fields:[
								{array:{min:data.resources.length,max:data.resources.length},label: '', name: 'resources', type: 'fieldset', fields:[
									{label:false, name: 'name',columns:4, type:'output', format:{value:'<label class="control-label" style="float:right">{{value}}: </lable>'}},
									{name: 'resource',label:false,columns:8, type: 'select', options: '/api/proxy/'+slug+'/resources/type/'+api.environment.type,format:{label:"{{name}}",value:"{{id}}"}},
									{label:false, name: 'name',columns:0, type:'hidden'}
								]}			
					]
					},'.resources')


				}else{
					$('#resourcestab').hide();
				}

		var routes_partials = []
		_.map(_.pluck(api.api_version.routes,'path'),function(item){
			var thisTemp = '';
			var parts = item.split('/')
			for (var i = 0, len = parts.length; i < len; i++) {
				if(parts[i]!== '*'){
					// if((i+1)<len){
					// thisTemp += parts[i]+'/';
					// }else{
						thisTemp += parts[i]
					// }
					routes_partials.push(thisTemp);	
					// routes_partials.push({value:thisTemp,label:thisTemp+'*'});	

					thisTemp += '/';			
				}		
			}
		})
		routes_partials = _.map(_.uniq(routes_partials),function(item){
			return {value:item,label:item+'*'}
		})

		var options = {
			el: '.permissions',

		title:'Permissions',

		name:"permissions",	
		schema:[
			{name: 'api_user',label:'Auth User', type: 'select', options: '/api/proxy/'+slug+'/environments/'+api.environment_id+'/api_users',format:{label:'{{app_name}}',value:'{{id}}'}},
							{label:'Path', name: 'route', type:'select', options:routes_partials},
							{label: 'Verb',name:'verb',type:'select',options:["ALL", "GET", "POST", "PUT", "DELETE"], required:true},
							{label:'Params',show:false,name:'params',template:'{{#attributes.params}}<b>{{name}}</b>:{{value}}<br>{{/attributes.params}}'}
		], data: api.route_user_map,
		actions:[
			{'name':'delete'},'|',
			{'name':'edit',max:1},{'name': 'params',max:1,min:1, 'label': '<i class="fa fa-info"></i> Parameters'},'|',
			{'name':'create'}
		]
	}
		grid = new GrapheneDataGrid(options)
		grid.on('model:params',function(e){
			new gform({
				name:'param_form',
				data:e.model.attributes,
				legend:'Parameters',
				fields:[
					
					{
						"name": "params",
						"label": false,
						array:true,
						type:'fieldset',
					
								fields:[
									{'name':'name','label':'Name',"inline":true,columns:6},
									{'name':'value','label':'Value',"inline":true,columns:6},
								]
							
						
					}
				]
				}).on('save',function(e){
					debugger;
					var temp = this.attributes;
					temp.params = e.form.get().params
					this.set(temp)
					this.draw();

					e.form.dispatch('close')
					
					// model.update
				}.bind(e.model)).on('cancel',function(e){e.form.dispatch('close')}).modal();
		})
		// $('.permissions').berry({
		// 	// legend:'Permissions',
		// 	name:'permissions',
		// 	attributes:api,
		// 	"flatten": false,
		// 	actions:false,
		// 	fields:[
		// 		{name:'container', label: false,  type: 'fieldset', fields:[
		// 			{"multiple": {"duplicate": true},label: '', name: 'route_user_map', type: 'fieldset', fields:[
		// 				{name: 'api_user',label:'Auth User', type: 'select', choices: '/api/proxy/'+slug+'/api_users',label_key:'app_name'},
		// 				{label:'Path', name: 'route', options:_.uniq(routes_partials)},
		// 				{label: 'Verb',name:'verb',type:'select',options:["ALL", "GET", "POST", "PUT", "DELETE"], required:true},
		// 				{
		// 					columns:6,
		// 					offset:4,
		// 					"name": "parameters",
		// 					"label": "Parameters",
		// 					"template":'{{#attributes.params}}{{#required}}<b>{{/required}}{{name}}{{#required}}</b>{{/required}}<br> {{/attributes.params}}',
		// 					"fields": {
		// 						"params": {
		// 							"label": false,
		// 							"flatten": true,
									
		// 							"multiple": {
		// 								"duplicate": true
		// 							},
		// 							fields:[
		// 								{'name':'name','label':'Name',"inline":true,columns:6},
		// 								{'name':'value','label':'Value',"inline":true,columns:6},
		// 							]
		// 						}
		// 					}
		// 				}
		// 			]}
		// 		]},			
		// 	]
		// 	})

			$('body').on('click','#version', function(){
				new gform({name:'versionForm',attributes:api,legend:'Select Version',fields:[
						{label: 'Version', name:'api_version_id', required:true, options:versions,type:'select', format:{value:'{{id}}',label:'{{label}}'}},
				]}).on('save',function(e){
					$.ajax({url: url, type: 'PUT', data: e.form.get(),
						success:function() {
							window.location.reload(true);
						},
						error:function(e) {
							toastr.error(e.statusText, 'ERROR');
						}
					});
				}).modal()
			})	

		}});




		$('#save').on('click',function(){
			if(_.findWhere(gform.collections.get('/api/proxy/'+slug+'/environments'),{id:parseInt(myapi.environment_id)}).type !== 'prod' || confirm('CAUTION: You are about to make updates in a production environment.\n\nWould you like to proceed?')){

        new gform({legend:'Update Comment', fields:[{name:'comment',label:'Comment',required:true}]}).on('save',function(e){
					api.comment = e.form.get().comment;
					$.extend(api,gform.instances.main.get());
					if(typeof gform.instances.resources !== 'undefined'){
						api.resources =  gform.instances.resources.get().resources;
					}
					// var temp = Berries.permissions.toJSON().container.route_user_map;
					// api.route_user_map = _.each(temp,function(item){
					// 	item.params = item.parameters.params;
					// 	delete item.parameters;
					// })
					api.route_user_map = grid.toJSON();
					
					
					$.ajax({url: url, type: 'PUT', data: api, success:function(){
							toastr.success('', 'Successfully updated API Instance')
						}.bind(this),
						error:function(e) {
							toastr.error(e.statusText, 'ERROR');
						}
					});
					e.form.dispatch('close')
				}).on('cancel',function(e){e.form.dispatch('close')}).modal()






			}
		})
	}
});


$(document).keydown(function(e) {
  if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey)) {
      e.preventDefault();
      $('#save').click()
  }
  return true;
});