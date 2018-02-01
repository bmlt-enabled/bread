tinymce.PluginManager.add('gavickpro_tc_button', function(editor) {

	editor.addButton('gavickpro_tc_button', {
		text: 'My test button',
		icon: false,
		onclick: function() {
			editor.insertContent('Hello World!');
		}
	});

});