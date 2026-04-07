export const CMN = {
	/**
	 * @param {number} bytes 
	 * @param {number} decimals
	 * @return {string} // for human readable
	 */
	formatBytes: (bytes, decimals = 2)=>{
		if (bytes === 0) {
			return '0';
		} else {
			var k = 1024;
			var dm = decimals < 0 ? 0 : decimals;
			var sizes = ['байт', 'КБ', 'МБ', 'ГБ', 'ТБ'];
			var i = Math.floor(Math.log(bytes) / Math.log(k));
			return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
		}
	}
};