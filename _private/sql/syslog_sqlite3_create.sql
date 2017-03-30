CREATE TABLE SystemEvents
(
        ID  integer NOT NULL PRIMARY KEY AUTOINCREMENT,
        CustomerID big integer,
        ReceivedAt datetime NULL,
        DeviceReportedTime datetime NULL,
        Facility small integer NULL,
        Priority small integer NULL,
        FromHost varchar(60) NULL,
        Message text,
        NTSeverity  integer NULL,
        Importance  integer NULL,
        EventSource varchar(60),
        EventUser varchar(60) NULL,
        EventCategory  integer NULL,
        EventID  integer NULL,
        EventBinaryData text NULL,
        MaxAvailable  integer NULL,
        CurrUsage  integer NULL,
        MinUsage  integer NULL,
        MaxUsage  integer NULL,
        InfoUnitID  integer NULL ,
        SysLogTag varchar(60),
        EventLogType varchar(60),
        GenericFileName VarChar(60),
        SystemID  integer NULL
);
CREATE TABLE SystemEventsProperties
(
        ID  integer NOT NULL PRIMARY KEY AUTOINCREMENT,
        SystemEventID  integer NULL ,
        ParamName varchar(255) NULL ,
        ParamValue text NULL
);