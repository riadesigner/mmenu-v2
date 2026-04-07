
export const IikoTables = {
    // @param {array} tables_data
    // @return {object}
    init:function(tables_data) {        
        this.tables_data = tables_data;
        return this;
    },
    get:function(prop) {
        return prop? this.tables_data[prop]: this.tables_data;
    },
    get_total_tables:function() {
        let arr_tables = [];
        const sections = this.tables_data;
        if(sections && sections.length) {
            for(let i=0; i<sections.length; i++) {
                if(sections[i].tables && sections[i].tables.length) {
                    arr_tables = [...arr_tables, ...sections[i].tables];
                }
            }
        };        
        console.log('arr_tables', arr_tables);
        console.log('total arr_tables', arr_tables.length);
        return arr_tables.length;
    }
};
