import {GLB} from './glb.js';

import {RDSAdmin} from './rds_admin.js';
import {RDSEnter} from './rds_enter.js';
import {RDSUserList} from './rds_user_list.js';
import {RDSAddContract} from './rds_add_contract.js';
import {RDS_MD5} from './rds_md5.js';


GLB.RDSAdmin = RDSAdmin;
GLB.RDSEnter = RDSEnter;
GLB.RDSUserList = RDSUserList;
GLB.RDSAddContract = RDSAddContract;
GLB.RDS_MD5 = RDS_MD5;


export default function(){

	$(function(){

		GLB.RDSAdmin.init();
		GLB.RDSEnter.init();
		GLB.RDSUserList.init();
		GLB.RDSAddContract.init();

	});
	
};