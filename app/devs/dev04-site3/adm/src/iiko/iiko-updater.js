
export var IikoUpdater = {
    init:function(opt) {        
        this.idMenuSaved = opt.idMenuSaved;        
        this.ID_CAFE = opt.id_cafe;
        this.roughMenuHash = opt.roughMenuHash;
                
        this.start_update({
            onReady:function(res) {
                console.log('res',res)
                opt.onReady&&opt.onReady("ok");
            },onError:function(errMsg) {
                opt.onError&&opt.onError(errMsg);
            }});
        return this;
    },
    start_update:function(opt){

        var PATH = 'adm/lib/iiko/';
        var url = PATH + 'iiko.upgrade_to_menu_from_iiko.php';
        
        var data ={
            id_cafe:this.ID_CAFE,
            id_menu_saved:this.idMenuSaved,
            rough_menu_hash: this.roughMenuHash
        };

        var errMsg = "cant upgrading to menu from iiko"; 

        this.AJAX = $.ajax({
            url: url+"?callback=?",
            dataType: "jsonp",
            data:data,
            method:"POST",
            success: function (result) {    
                console.log("success result upgrading menu = ",result);
                if(!result.error){
                    opt.onReady&&opt.onReady(result);
                }else{
                    console.log(errMsg);
                    opt.onError&&opt.onError(result);
                }
            },
            error:function(result) {
                console.log("failed result of upgrading menu = ",result);
                console.log(errMsg);
                opt.onError&&opt.onError(errMsg);
            }
        });
        
    }
};
