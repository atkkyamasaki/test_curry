<?php

class SyslogController extends RestController
{
    // オートリダイレクト有効
    protected $autoRedirect = true;
    // Severity
    private $severities = [
        0 => 'emerg',
        1 => 'alert',
        2 => 'crit',
        3 => 'err',
        4 => 'warning',
        5 => 'notice',
        6 => 'info',
        7 => 'debug',
    ];

    public function preProcess()
    {
        $ini = Ini::load('database_syslog.ini', $this->getAppEnv());
        Db::setConfig($ini);
    }

    public function index()
    {
        // severities
        $severities = $this->severities;

        // path
        $path = $this->request->getPath() === '' ? '/' : $this->request->getPath();

        // query
        $query = $this->restParams;

        // keyword
        $keyword = isset($query['search']) ? $query['search'] : null;

        // severity
        $severity = isset($query['severity']) ? $query['severity'] : null;

        // page
        $page = isset($query['page']) ? $query['page'] : 1;

        // limit
        $limit = isset($query['limit']) ? $query['limit'] : 250;

        // results per page
        $resultsPerPage = $limit;

        // fields
        $fields = [
            'ID',
//            'CustomerID',
//            'ReceivedAt',
            'DeviceReportedTime',
            'Facility',
            'Priority',
            'FromHost',
            'Message',
//            'NTSeverity',
//            'Importance',
//            'EventSource',
//            'EventUser',
//            'EventCategory',
//            'EventID',
//            'EventBinaryData',
//            'MaxAvailable',
//            'CurrUsage',
//            'MinUsage',
//            'MaxUsage',
//            'InfoUnitID',
            'SysLogTag',
//            'EventLogType',
//            'GenericFileName',
//            'SystemID',
        ];

        if (isset($severity) && $severity !== '8') {
            $where = [
                'Priority' => $severity,
            ];
        } else {
            $where = null;
        }

        // DBのテーブル命名規則をパスカルケースに変更
        NameManager::setTableCase(NameCase::PASCAL);
        $systemEventsModel = $this->model('SystemEvents');
        // 結果数をカウント
        $systemEventsCount = $systemEventsModel->count($keyword);

        // パージネーターを設定
        $paginator = new Paginator();
        // クエリを設定
        $paginator->setQuery($query);
        // パージネイト
        $paginator->paginate($page, $systemEventsCount, $resultsPerPage);
        // SystemEventsを取得
        $systemEvents = $systemEventsModel->get($where, $fields, $keyword, $paginator->getOffset(), $limit);

        $this->view->severities = $severities;
        $this->view->path = $path;
        $this->view->query = $query;
        $this->view->fields = $fields;
        $this->view->where = $where;
        $this->view->limit = $limit;
        $this->view->keyword = $keyword;
        $this->view->severity = $severity;
        $this->view->system_events = $systemEvents;
        $this->view->paginator = $paginator->getPaginator();

        // 現在のページ
        // 結果総数
        // 1ページあたりの表示件数
        // ページタイトル
        $this->view->setTitle('Syslog');
    }

    public function post()
    {
        if (isset($this->restParams['syslog']) && isset($this->restParams['severity'])) {
            syslog($this->restParams['severity'], $this->restParams['syslog']);
        }
    }

    public function put()
    {
//        echo '<a href="/test">back</a>';
//        echo '<hr>';
        // DBのテーブル命名規則をパスカルケースに変更
        NameManager::setTableCase(NameCase::PASCAL);
        $systemEventsModel = $this->model('SystemEvents');

        // SystemEventsを取得
        $systemEvents = $systemEventsModel->get();

        // CSVファイル保存先
        $directory = PathManager::getHtdocsDirectory() . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR . 'syslog';
//        var_dump($directory);
        // CSV操作
        $csvManipulator = new CsvManipulator();
        $csv = $csvManipulator->arrayToCsv($systemEvents);

        // CSV保存
        $timestamp = time();
        $filename = 'syslog_' . $timestamp . '.csv';
        $filepath = $directory . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($filepath, $csv);

        // ダウンロードさせる
        header('Content-Type: application/force-download');
        header('Content-Length: ' . filesize($filepath));
        header('Content-disposition: attachment; filename="' . $filename . '"');
        readfile($filepath);
    }
//    Severity
//    Value	Severity	Keyword	Description	Examples
//    0	Emergency	emerg	System is unusable	This level should not be used by applications.
//    1	Alert	alert	Should be corrected immediately	Loss of the primary ISP connection.
//    2	Critical	crit	Critical conditions	A failure in the system's primary application.
//    3	Error	err	Error conditions	An application has exceeded its file storage limit and attempts to write are failing.
//    4	Warning	warning	May indicate that an error will occur if action is not taken.	A non-root file system has only 2GB remaining.
//    5	Notice	notice	Events that are unusual, but not error conditions.	
//    6	Informational	info	Normal operational messages that require no action.	An application has started, paused or ended successfully.
//    7	Debug	debug	Information useful to developers for debugging the application.	
//
//    Facility
//    Facility code	Keyword	Description
//    0	kern	kernel messages
//    1	user	user-level messages
//    2	mail	mail system
//    3	daemon	system daemons
//    4	auth	security/authorization messages
//    5	syslog	messages generated internally by syslogd
//    6	lpr	line printer subsystem
//    7	news	network news subsystem
//    8	uucp	UUCP subsystem
//    9		clock daemon
//    10	authpriv	security/authorization messages
//    11	ftp	FTP daemon
//    12	-	NTP subsystem
//    13	-	log audit
//    14	-	log alert
//    15	cron	scheduling daemon
//    16	local0	local use 0 (local0)
//    17	local1	local use 1 (local1)
//    18	local2	local use 2 (local2)
//    19	local3	local use 3 (local3)
//    20	local4	local use 4 (local4)
//    21	local5	local use 5 (local5)
//    22	local6	local use 6 (local6)
//    23	local7	local use 7 (local7)

}