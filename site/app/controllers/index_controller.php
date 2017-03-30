<?php

class IndexController extends RestController
{

    public function index()
    {
        // ページタイトル
        $this->view->setTitle('Dashboard');
        
        // TEST
//        $valuemappingService = $this->service('ValuemappingService');
        // Export
//        $json = $valuemappingService->export();
        
//        $json = '[{"valuemapid":"4","name":"APC Battery Replacement Status","mappings":[{"mappingid":"26","valuemapid":"4","value":"1","newvalue":"unknown"},{"mappingid":"27","valuemapid":"4","value":"2","newvalue":"notInstalled"},{"mappingid":"28","valuemapid":"4","value":"3","newvalue":"ok"},{"mappingid":"29","valuemapid":"4","value":"4","newvalue":"failed"},{"mappingid":"30","valuemapid":"4","value":"5","newvalue":"highTemperature"},{"mappingid":"31","valuemapid":"4","value":"6","newvalue":"replaceImmediately"},{"mappingid":"32","valuemapid":"4","value":"7","newvalue":"lowCapacity"}]},{"valuemapid":"5","name":"APC Battery Status","mappings":[{"mappingid":"23","valuemapid":"5","value":"1","newvalue":"unknown"},{"mappingid":"24","valuemapid":"5","value":"2","newvalue":"batteryNormal"},{"mappingid":"25","valuemapid":"5","value":"3","newvalue":"batteryLow"}]},{"valuemapid":"7","name":"Dell Open Manage System Status","mappings":[{"mappingid":"17","valuemapid":"7","value":"1","newvalue":"Other"},{"mappingid":"18","valuemapid":"7","value":"2","newvalue":"Unknown"},{"mappingid":"19","valuemapid":"7","value":"3","newvalue":"OK"},{"mappingid":"20","valuemapid":"7","value":"4","newvalue":"NonCritical"},{"mappingid":"21","valuemapid":"7","value":"5","newvalue":"Critical"},{"mappingid":"22","valuemapid":"7","value":"6","newvalue":"NonRecoverable"}]},{"valuemapid":"6","name":"HP Insight System Status","mappings":[{"mappingid":"13","valuemapid":"6","value":"1","newvalue":"Other"},{"mappingid":"14","valuemapid":"6","value":"2","newvalue":"OK"},{"mappingid":"15","valuemapid":"6","value":"3","newvalue":"Degraded"}]},{"valuemapid":"2","name":"Host status","mappings":[{"mappingid":"3","valuemapid":"2","value":"0","newvalue":"Up"},{"mappingid":"4","valuemapid":"2","value":"2","newvalue":"Unreachable"}]},{"valuemapid":"9","name":"SNMP device status (hrDeviceStatus)","mappings":[{"mappingid":"49","valuemapid":"9","value":"1","newvalue":"unknown"},{"mappingid":"50","valuemapid":"9","value":"2","newvalue":"running"},{"mappingid":"51","valuemapid":"9","value":"3","newvalue":"warning"},{"mappingid":"52","valuemapid":"9","value":"4","newvalue":"testing"},{"mappingid":"53","valuemapid":"9","value":"5","newvalue":"down"}]},{"valuemapid":"11","name":"SNMP interface status (ifAdminStatus)","mappings":[{"mappingid":"69","valuemapid":"11","value":"1","newvalue":"up"},{"mappingid":"70","valuemapid":"11","value":"2","newvalue":"down"},{"mappingid":"71","valuemapid":"11","value":"3","newvalue":"testing"}]},{"valuemapid":"8","name":"SNMP interface status (ifOperStatus)","mappings":[{"mappingid":"61","valuemapid":"8","value":"1","newvalue":"up"},{"mappingid":"62","valuemapid":"8","value":"2","newvalue":"down"},{"mappingid":"63","valuemapid":"8","value":"3","newvalue":"testing"},{"mappingid":"64","valuemapid":"8","value":"4","newvalue":"unknown"},{"mappingid":"65","valuemapid":"8","value":"5","newvalue":"dormant"},{"mappingid":"66","valuemapid":"8","value":"6","newvalue":"notPresent"},{"mappingid":"67","valuemapid":"8","value":"7","newvalue":"lowerLayerDown"}]},{"valuemapid":"1","name":"Service state","mappings":[{"mappingid":"1","valuemapid":"1","value":"0","newvalue":"Down"},{"mappingid":"2","valuemapid":"1","value":"1","newvalue":"Up"}]},{"valuemapid":"12","name":"VMware VirtualMachinePowerState","mappings":[{"mappingid":"72","valuemapid":"12","value":"0","newvalue":"poweredOff"},{"mappingid":"73","valuemapid":"12","value":"1","newvalue":"poweredOn"},{"mappingid":"74","valuemapid":"12","value":"2","newvalue":"suspended"}]},{"valuemapid":"13","name":"VMware status","mappings":[{"mappingid":"75","valuemapid":"13","value":"0","newvalue":"gray"},{"mappingid":"76","valuemapid":"13","value":"1","newvalue":"green"},{"mappingid":"77","valuemapid":"13","value":"2","newvalue":"yellow"},{"mappingid":"78","valuemapid":"13","value":"3","newvalue":"red"}]},{"valuemapid":"3","name":"Windows service state","mappings":[{"mappingid":"33","valuemapid":"3","value":"0","newvalue":"Running"},{"mappingid":"34","valuemapid":"3","value":"1","newvalue":"Paused"},{"mappingid":"35","valuemapid":"3","value":"3","newvalue":"Pause pending"},{"mappingid":"36","valuemapid":"3","value":"4","newvalue":"Continue pending"},{"mappingid":"37","valuemapid":"3","value":"5","newvalue":"Stop pending"},{"mappingid":"38","valuemapid":"3","value":"6","newvalue":"Stopped"},{"mappingid":"39","valuemapid":"3","value":"7","newvalue":"Unknown"},{"mappingid":"40","valuemapid":"3","value":"255","newvalue":"No such service"},{"mappingid":"41","valuemapid":"3","value":"2","newvalue":"Start pending"}]},{"valuemapid":"10","name":"Zabbix agent ping status","mappings":[{"mappingid":"68","valuemapid":"10","value":"1","newvalue":"Up"}]}]';
//        
        // Import
//        $valuemappingService->import($json);
    }

    public function post()
    {
        if ($this->request->isXmlHttp()) {
            $this->response->json($this->restParams);
        }
    }

}