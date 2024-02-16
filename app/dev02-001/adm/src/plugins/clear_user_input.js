
export default 
$.clear_user_input = function (str,max_length) {

		var str = str;
		var max_length = max_length?max_length:100;
        
		// polyfill trim(), вырезаем BOM и неразрывный пробел
        str = str.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
        // max length
		str = str.substr(0, max_length );
		// strip html
        str = str.replace(/<[^>]*>?/gm, '');
                
        return str;

};