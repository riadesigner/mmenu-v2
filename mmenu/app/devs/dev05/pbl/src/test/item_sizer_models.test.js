import {IIKO_ITEM_SIZER} from '../iiko/iiko-item-sizer.js';
import {CHEFS_ITEM_SIZER} from '../chefs/chefs-item-sizer.js';
 
describe('ITEM SIZER MODELS HAS THE PUBLIC METHODS', ()=>{

    const methods = [
        'init',
        'reset',
        'get_ui',
        'get',
        'get_all'
    ];

    test('IIKO_ITEM_SIZER has the public methods', () => {
        for(let i in methods){
            expect(IIKO_ITEM_SIZER).toHaveProperty(methods[i]);    
        }
      });
    test('CHEFS_ITEM_SIZER has the public methods', () => {
        for(let i in methods){
            expect(CHEFS_ITEM_SIZER).toHaveProperty(methods[i]);    
        }
    });      

});