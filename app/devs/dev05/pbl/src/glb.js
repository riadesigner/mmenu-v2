export var GLB = {};

GLB.public_menu_api_url = function() {
	const raw = (typeof SITE_CFG !== "undefined" && SITE_CFG.public_menu_api)
		? String(SITE_CFG.public_menu_api)
		: "https://chefsmenu.ru/api/v1";
	return raw.replace(/\/+$/, "");
};
 