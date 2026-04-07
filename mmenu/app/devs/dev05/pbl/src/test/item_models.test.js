import {IIKO_ITEM} from '../iiko/iiko-item.js';
import {CHEFS_ITEM} from '../chefs/chefs-item.js';
 
describe('ITEM MODELS HAS THE PUBLIC METHODS', ()=>{

    const methods = [
        'get',
        'has_modifiers',
        'has_sizes',
        'get_preorder',
        'get_price',
        'sizer_get_vars',
        'get_ui_price_buttons'
    ];

    test('IIKO_ITEM has the public methods', () => {
        for(let i in methods){
            expect(IIKO_ITEM).toHaveProperty(methods[i]);    
        }
      });
    test('CHEFS_ITEM has the public methods', () => {
        for(let i in methods){
            expect(CHEFS_ITEM).toHaveProperty(methods[i]);    
        }
    });      

});