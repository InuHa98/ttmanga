function addPluginSmiles(name = null, images = [], tooltip = null)
{
	if(!name || images.length < 1)
	{
		return;
	}
	
	tinymce.PluginManager.add(name, function(editor) {
		function buildHTML() {
			if(images.length < 1){
				return null;
			}


			var container = $("#editor_ifr");
			const width = container.width(),
				  height = container.height();

			var html = '<div role="presentation" class="mce-smiles-box" style="height: '+height+'px; width: '+width+'px">';
			tinymce.each(images, function(column){
				if(typeof column === "object")
				{
					tinymce.each(column, function(row){
						html += '<div class="mce-smiles-item" data-smile-url="' + row + '"><img src="' + row + '"></div>';
					});					
				}
				else
				{
					html += '<div class="mce-smiles-item" data-smile-url="' + column + '"><img src="' + column + '"></div>';
				}
			});
			html += '</div>';
			return html;
		}

		editor.addButton(name, {
			type: "panelbutton",
			image: images[0] || images[0][0],
			classes: "smiles-button",
			panel: {
				classes: "smiles-panel",
				autohide: false,
				html: buildHTML,
				onclick: function(data) {
					var target = editor.dom.getParent(data.target, ".mce-smiles-item");
					if(target)
					{
						editor.insertContent('<img alt="emo" src="' + target.getAttribute("data-smile-url") + '" />');
						this.hide();
					}
				}
			},
			tooltip: tooltip || name
		});
	});
}