--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.5

-- Started on 2025-07-05 14:01:03 CST

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 217 (class 1259 OID 16936)
-- Name: alog; Type: TABLE; Schema: public; Owner: t1
--

CREATE TABLE public.alog (
    id integer NOT NULL,
    type character varying(100) DEFAULT ''::character varying NOT NULL,
    log1 text,
    log2 text,
    log3 text
);


ALTER TABLE public.alog OWNER TO t1;

--
-- TOC entry 3431 (class 0 OID 0)
-- Dependencies: 217
-- Name: TABLE alog; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON TABLE public.alog IS 'log';


--
-- TOC entry 218 (class 1259 OID 16942)
-- Name: alog_id_seq; Type: SEQUENCE; Schema: public; Owner: t1
--

CREATE SEQUENCE public.alog_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.alog_id_seq OWNER TO t1;

--
-- TOC entry 3432 (class 0 OID 0)
-- Dependencies: 218
-- Name: alog_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: t1
--

ALTER SEQUENCE public.alog_id_seq OWNED BY public.alog.id;


--
-- TOC entry 219 (class 1259 OID 16943)
-- Name: api_request_log; Type: TABLE; Schema: public; Owner: t1
--

CREATE TABLE public.api_request_log (
    id integer NOT NULL,
    url character varying(250) DEFAULT ''::character varying NOT NULL,
    params character varying(1500) NOT NULL,
    postdata text NOT NULL,
    ip character varying(40) DEFAULT ''::character varying NOT NULL,
    addtime bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE public.api_request_log OWNER TO t1;

--
-- TOC entry 3433 (class 0 OID 0)
-- Dependencies: 219
-- Name: TABLE api_request_log; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON TABLE public.api_request_log IS 'API接口请求日志';


--
-- TOC entry 3434 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN api_request_log.url; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON COLUMN public.api_request_log.url IS '请求方法';


--
-- TOC entry 3435 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN api_request_log.params; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON COLUMN public.api_request_log.params IS 'GET参数';


--
-- TOC entry 3436 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN api_request_log.postdata; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON COLUMN public.api_request_log.postdata IS '请求BODY';


--
-- TOC entry 3437 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN api_request_log.ip; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON COLUMN public.api_request_log.ip IS 'IP';


--
-- TOC entry 220 (class 1259 OID 16951)
-- Name: api_request_log_id_seq; Type: SEQUENCE; Schema: public; Owner: t1
--

CREATE SEQUENCE public.api_request_log_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.api_request_log_id_seq OWNER TO t1;

--
-- TOC entry 3438 (class 0 OID 0)
-- Dependencies: 220
-- Name: api_request_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: t1
--

ALTER SEQUENCE public.api_request_log_id_seq OWNED BY public.api_request_log.id;


--
-- TOC entry 221 (class 1259 OID 16952)
-- Name: app_secret; Type: TABLE; Schema: public; Owner: t1
--

CREATE TABLE public.app_secret (
    id integer NOT NULL,
    appid character varying(32) DEFAULT ''::character varying NOT NULL,
    appsecret character varying(100) DEFAULT ''::character varying NOT NULL,
    title character varying(16) DEFAULT ''::character varying NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    expires bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE public.app_secret OWNER TO t1;

--
-- TOC entry 3439 (class 0 OID 0)
-- Dependencies: 221
-- Name: TABLE app_secret; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON TABLE public.app_secret IS '项目对接密钥';


--
-- TOC entry 3440 (class 0 OID 0)
-- Dependencies: 221
-- Name: COLUMN app_secret.title; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON COLUMN public.app_secret.title IS '名称';


--
-- TOC entry 3441 (class 0 OID 0)
-- Dependencies: 221
-- Name: COLUMN app_secret.status; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON COLUMN public.app_secret.status IS '0禁用，1生效，2期限';


--
-- TOC entry 222 (class 1259 OID 16960)
-- Name: app_secret_id_seq; Type: SEQUENCE; Schema: public; Owner: t1
--

CREATE SEQUENCE public.app_secret_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.app_secret_id_seq OWNER TO t1;

--
-- TOC entry 3442 (class 0 OID 0)
-- Dependencies: 222
-- Name: app_secret_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: t1
--

ALTER SEQUENCE public.app_secret_id_seq OWNED BY public.app_secret.id;


--
-- TOC entry 223 (class 1259 OID 16961)
-- Name: ws_clients; Type: TABLE; Schema: public; Owner: t1
--

CREATE TABLE public.ws_clients (
    fd integer NOT NULL,
    addtime bigint DEFAULT '0'::bigint NOT NULL,
    remote_addr character varying(42) DEFAULT ''::character varying NOT NULL,
    sec_key character varying(64) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.ws_clients OWNER TO t1;

--
-- TOC entry 3443 (class 0 OID 0)
-- Dependencies: 223
-- Name: TABLE ws_clients; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON TABLE public.ws_clients IS 'WebSocket 在线表';


--
-- TOC entry 3444 (class 0 OID 0)
-- Dependencies: 223
-- Name: COLUMN ws_clients.addtime; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON COLUMN public.ws_clients.addtime IS '上线时间';


--
-- TOC entry 3445 (class 0 OID 0)
-- Dependencies: 223
-- Name: COLUMN ws_clients.remote_addr; Type: COMMENT; Schema: public; Owner: t1
--

COMMENT ON COLUMN public.ws_clients.remote_addr IS '来源IP';


--
-- TOC entry 3250 (class 2604 OID 16967)
-- Name: alog id; Type: DEFAULT; Schema: public; Owner: t1
--

ALTER TABLE ONLY public.alog ALTER COLUMN id SET DEFAULT nextval('public.alog_id_seq'::regclass);


--
-- TOC entry 3252 (class 2604 OID 16968)
-- Name: api_request_log id; Type: DEFAULT; Schema: public; Owner: t1
--

ALTER TABLE ONLY public.api_request_log ALTER COLUMN id SET DEFAULT nextval('public.api_request_log_id_seq'::regclass);


--
-- TOC entry 3256 (class 2604 OID 16969)
-- Name: app_secret id; Type: DEFAULT; Schema: public; Owner: t1
--

ALTER TABLE ONLY public.app_secret ALTER COLUMN id SET DEFAULT nextval('public.app_secret_id_seq'::regclass);


--
-- TOC entry 3419 (class 0 OID 16936)
-- Dependencies: 217
-- Data for Name: alog; Type: TABLE DATA; Schema: public; Owner: t1
--

COPY public.alog (id, type, log1, log2, log3) FROM stdin;
\.


--
-- TOC entry 3421 (class 0 OID 16943)
-- Dependencies: 219
-- Data for Name: api_request_log; Type: TABLE DATA; Schema: public; Owner: t1
--

COPY public.api_request_log (id, url, params, postdata, ip, addtime) FROM stdin;
\.


--
-- TOC entry 3423 (class 0 OID 16952)
-- Dependencies: 221
-- Data for Name: app_secret; Type: TABLE DATA; Schema: public; Owner: t1
--

COPY public.app_secret (id, appid, appsecret, title, status, expires) FROM stdin;
1	app313276672646586985	481b9e180527e3ce790e85b43369ce64	测试项目	1	0
\.


--
-- TOC entry 3425 (class 0 OID 16961)
-- Dependencies: 223
-- Data for Name: ws_clients; Type: TABLE DATA; Schema: public; Owner: t1
--

COPY public.ws_clients (fd, addtime, remote_addr, sec_key) FROM stdin;
\.


--
-- TOC entry 3446 (class 0 OID 0)
-- Dependencies: 218
-- Name: alog_id_seq; Type: SEQUENCE SET; Schema: public; Owner: t1
--

SELECT pg_catalog.setval('public.alog_id_seq', 1, true);


--
-- TOC entry 3447 (class 0 OID 0)
-- Dependencies: 220
-- Name: api_request_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: t1
--

SELECT pg_catalog.setval('public.api_request_log_id_seq', 154, true);


--
-- TOC entry 3448 (class 0 OID 0)
-- Dependencies: 222
-- Name: app_secret_id_seq; Type: SEQUENCE SET; Schema: public; Owner: t1
--

SELECT pg_catalog.setval('public.app_secret_id_seq', 1, true);


--
-- TOC entry 3266 (class 2606 OID 16971)
-- Name: alog idx_16752_primary; Type: CONSTRAINT; Schema: public; Owner: t1
--

ALTER TABLE ONLY public.alog
    ADD CONSTRAINT idx_16752_primary PRIMARY KEY (id);


--
-- TOC entry 3268 (class 2606 OID 16973)
-- Name: api_request_log idx_16760_primary; Type: CONSTRAINT; Schema: public; Owner: t1
--

ALTER TABLE ONLY public.api_request_log
    ADD CONSTRAINT idx_16760_primary PRIMARY KEY (id);


--
-- TOC entry 3271 (class 2606 OID 16975)
-- Name: app_secret idx_16770_primary; Type: CONSTRAINT; Schema: public; Owner: t1
--

ALTER TABLE ONLY public.app_secret
    ADD CONSTRAINT idx_16770_primary PRIMARY KEY (id);


--
-- TOC entry 3273 (class 2606 OID 16977)
-- Name: ws_clients idx_16779_primary; Type: CONSTRAINT; Schema: public; Owner: t1
--

ALTER TABLE ONLY public.ws_clients
    ADD CONSTRAINT idx_16779_primary PRIMARY KEY (fd);


--
-- TOC entry 3269 (class 1259 OID 16978)
-- Name: idx_16770_appid; Type: INDEX; Schema: public; Owner: t1
--

CREATE UNIQUE INDEX idx_16770_appid ON public.app_secret USING btree (appid);


-- Completed on 2025-07-05 14:01:04 CST

--
-- PostgreSQL database dump complete
--

