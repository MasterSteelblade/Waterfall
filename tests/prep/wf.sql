--
-- PostgreSQL database dump
--

-- Dumped from database version 13.2 (Ubuntu 13.2-1.pgdg20.04+1)
-- Dumped by pg_dump version 13.2

-- Started on 2021-03-03 07:24:51

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 675 (class 1247 OID 16388)
-- Name: account_flags; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.account_flags AS ENUM (
    'setup_complete'
);


ALTER TYPE public.account_flags OWNER TO waterfall_user;

--
-- TOC entry 678 (class 1247 OID 16392)
-- Name: account_restrictions; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.account_restrictions AS ENUM (
    'art',
    'asks',
    'commissions'
);


ALTER TYPE public.account_restrictions OWNER TO waterfall_user;

--
-- TOC entry 681 (class 1247 OID 16400)
-- Name: account_types; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.account_types AS ENUM (
    'user',
    'banned',
    'admin',
    'staff',
    'moderator',
    'ex-staff',
    'ex-moderator'
);


ALTER TYPE public.account_types OWNER TO waterfall_user;

--
-- TOC entry 684 (class 1247 OID 16416)
-- Name: badge_scope; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.badge_scope AS ENUM (
    'account',
    'blog'
);


ALTER TYPE public.badge_scope OWNER TO waterfall_user;

--
-- TOC entry 687 (class 1247 OID 16422)
-- Name: badge_types; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.badge_types AS ENUM (
    'staff',
    'award',
    'achievement',
    'conditional',
    'pride',
    'special'
);


ALTER TYPE public.badge_types OWNER TO waterfall_user;

--
-- TOC entry 690 (class 1247 OID 16436)
-- Name: blog_permissions; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.blog_permissions AS ENUM (
    'write_post',
    'edit_post',
    'delete_post',
    'read_asks',
    'answer_asks',
    'delete_asks',
    'create_page',
    'edit_page',
    'delete_page',
    'change_password',
    'change_theme',
    'follow_list',
    'like_list',
    'blog_settings',
    'send_asks'
);


ALTER TYPE public.blog_permissions OWNER TO waterfall_user;

--
-- TOC entry 693 (class 1247 OID 16468)
-- Name: commission_approval; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.commission_approval AS ENUM (
    'unapproved',
    'artist_approved',
    'buyer_approved',
    'finalised'
);


ALTER TYPE public.commission_approval OWNER TO waterfall_user;

--
-- TOC entry 696 (class 1247 OID 16478)
-- Name: commission_status; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.commission_status AS ENUM (
    'pending',
    'declined',
    'cancelled',
    'accepted',
    'finished',
    'ongoing'
);


ALTER TYPE public.commission_status OWNER TO waterfall_user;

--
-- TOC entry 699 (class 1247 OID 16492)
-- Name: entitlement_type; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.entitlement_type AS ENUM (
    'badge',
    'theme',
    'slots',
    'subscription',
    'pack'
);


ALTER TYPE public.entitlement_type OWNER TO waterfall_user;

--
-- TOC entry 702 (class 1247 OID 16504)
-- Name: feedback_type; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.feedback_type AS ENUM (
    'negative',
    'neutral',
    'positive'
);


ALTER TYPE public.feedback_type OWNER TO waterfall_user;

--
-- TOC entry 705 (class 1247 OID 16512)
-- Name: message_type; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.message_type AS ENUM (
    'ask',
    'submission',
    'system',
    'admin',
    'promotional'
);


ALTER TYPE public.message_type OWNER TO waterfall_user;

--
-- TOC entry 708 (class 1247 OID 16524)
-- Name: notification_type; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.notification_type AS ENUM (
    'like',
    'reblog',
    'answer',
    'follow',
    'mention',
    'comment'
);


ALTER TYPE public.notification_type OWNER TO waterfall_user;

--
-- TOC entry 711 (class 1247 OID 16538)
-- Name: page_type; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.page_type AS ENUM (
    'text',
    'html'
);


ALTER TYPE public.page_type OWNER TO waterfall_user;

--
-- TOC entry 714 (class 1247 OID 16544)
-- Name: poll_type; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.poll_type AS ENUM (
    'single',
    'multiple'
);


ALTER TYPE public.poll_type OWNER TO waterfall_user;

--
-- TOC entry 717 (class 1247 OID 16550)
-- Name: post_status; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.post_status AS ENUM (
    'posted',
    'draft',
    'private',
    'deleted',
    'transcoding'
);


ALTER TYPE public.post_status OWNER TO waterfall_user;

--
-- TOC entry 720 (class 1247 OID 16562)
-- Name: post_type; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.post_type AS ENUM (
    'text',
    'message',
    'image',
    'art',
    'video',
    'audio',
    'link',
    'quote',
    'chat',
    'poll'
);


ALTER TYPE public.post_type OWNER TO waterfall_user;

--
-- TOC entry 723 (class 1247 OID 16584)
-- Name: pronoun_sets; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.pronoun_sets AS ENUM (
    'they/them',
    'she/her',
    'he/him'
);


ALTER TYPE public.pronoun_sets OWNER TO waterfall_user;

--
-- TOC entry 726 (class 1247 OID 16592)
-- Name: raven_role; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.raven_role AS ENUM (
    'master',
    'content'
);


ALTER TYPE public.raven_role OWNER TO waterfall_user;

--
-- TOC entry 729 (class 1247 OID 16598)
-- Name: raven_status; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.raven_status AS ENUM (
    'online',
    'offline',
    'full',
    'decommissioning',
    'maintenance',
    'error'
);


ALTER TYPE public.raven_status OWNER TO waterfall_user;

--
-- TOC entry 732 (class 1247 OID 16612)
-- Name: subscription_tiers; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.subscription_tiers AS ENUM (
    'free',
    'freshwater',
    'groundwater',
    'springwater'
);


ALTER TYPE public.subscription_tiers OWNER TO waterfall_user;

--
-- TOC entry 735 (class 1247 OID 16622)
-- Name: transcode_status; Type: TYPE; Schema: public; Owner: waterfall_user
--

CREATE TYPE public.transcode_status AS ENUM (
    'waiting',
    'in_progress',
    'complete',
    'failed'
);


ALTER TYPE public.transcode_status OWNER TO waterfall_user;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 200 (class 1259 OID 16631)
-- Name: analytics; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.analytics (
    id bigint NOT NULL,
    data jsonb
);


ALTER TABLE public.analytics OWNER TO waterfall_user;

--
-- TOC entry 201 (class 1259 OID 16637)
-- Name: analytics_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.analytics_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.analytics_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3358 (class 0 OID 0)
-- Dependencies: 201
-- Name: analytics_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.analytics_id_seq OWNED BY public.analytics.id;


--
-- TOC entry 202 (class 1259 OID 16639)
-- Name: art_data_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.art_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.art_data_id_seq OWNER TO waterfall_user;

--
-- TOC entry 203 (class 1259 OID 16641)
-- Name: art_data; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.art_data (
    id bigint DEFAULT nextval('public.art_data_id_seq'::regclass) NOT NULL,
    post_id bigint NOT NULL,
    on_blog bigint,
    image_id bigint NOT NULL,
    image_md5 text[]
);


ALTER TABLE public.art_data OWNER TO waterfall_user;

--
-- TOC entry 204 (class 1259 OID 16655)
-- Name: audio; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.audio (
    id bigint NOT NULL,
    paths jsonb,
    artist text,
    title text,
    servers integer[],
    md5 text,
    duration_minutes text,
    duration_seconds text,
    album_art integer DEFAULT 0
);


ALTER TABLE public.audio OWNER TO waterfall_user;

--
-- TOC entry 205 (class 1259 OID 16662)
-- Name: audio_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.audio_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.audio_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3359 (class 0 OID 0)
-- Dependencies: 205
-- Name: audio_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.audio_id_seq OWNED BY public.audio.id;


--
-- TOC entry 206 (class 1259 OID 16664)
-- Name: badges; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.badges (
    id integer NOT NULL,
    short_name text,
    nice_name text,
    description text,
    filename text,
    badge_type public.badge_types DEFAULT 'award'::public.badge_types,
    scope public.badge_scope DEFAULT 'account'::public.badge_scope,
    default_badge boolean DEFAULT false
);


ALTER TABLE public.badges OWNER TO waterfall_user;

--
-- TOC entry 207 (class 1259 OID 16673)
-- Name: badges_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.badges_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.badges_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3360 (class 0 OID 0)
-- Dependencies: 207
-- Name: badges_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.badges_id_seq OWNED BY public.badges.id;


--
-- TOC entry 208 (class 1259 OID 16675)
-- Name: blog_members_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.blog_members_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.blog_members_id_seq OWNER TO waterfall_user;

--
-- TOC entry 209 (class 1259 OID 16677)
-- Name: blog_members; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.blog_members (
    id bigint DEFAULT nextval('public.blog_members_id_seq'::regclass) NOT NULL,
    blog_id bigint NOT NULL,
    user_id bigint NOT NULL,
    confirmed boolean,
    join_key text,
    joined timestamp without time zone,
    permissions public.blog_permissions[] DEFAULT '{}'::public.blog_permissions[]
);


ALTER TABLE public.blog_members OWNER TO waterfall_user;

--
-- TOC entry 210 (class 1259 OID 16685)
-- Name: blogs; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.blogs (
    id bigint NOT NULL,
    owner_id bigint,
    blog_name text,
    blog_title text,
    blog_description text,
    adult_only boolean,
    allow_search boolean,
    password text,
    ask_level integer,
    settings jsonb,
    pinned_post bigint,
    badges integer[],
    created timestamp without time zone,
    avatar bigint,
    badges_allowed integer[] DEFAULT '{}'::integer[],
    theme integer DEFAULT 1
);


ALTER TABLE public.blogs OWNER TO waterfall_user;

--
-- TOC entry 211 (class 1259 OID 16693)
-- Name: blogs_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.blogs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.blogs_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3361 (class 0 OID 0)
-- Dependencies: 211
-- Name: blogs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.blogs_id_seq OWNED BY public.blogs.id;


--
-- TOC entry 212 (class 1259 OID 16709)
-- Name: entitlements; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.entitlements (
    id integer NOT NULL,
    name text NOT NULL,
    description text NOT NULL,
    is_pack boolean,
    pack_contents integer[],
    badge_id integer,
    theme_id integer,
    subscription_type public.subscription_tiers,
    subscription_months integer,
    slot_count integer,
    entitlement_type public.entitlement_type
);


ALTER TABLE public.entitlements OWNER TO waterfall_user;

--
-- TOC entry 213 (class 1259 OID 16715)
-- Name: featured_posts; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.featured_posts (
    id bigint NOT NULL,
    post_id bigint,
    on_blog bigint
);


ALTER TABLE public.featured_posts OWNER TO waterfall_user;

--
-- TOC entry 214 (class 1259 OID 16718)
-- Name: featured_posts_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.featured_posts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.featured_posts_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3362 (class 0 OID 0)
-- Dependencies: 214
-- Name: featured_posts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.featured_posts_id_seq OWNED BY public.featured_posts.id;


--
-- TOC entry 215 (class 1259 OID 16720)
-- Name: follows; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.follows (
    id bigint NOT NULL,
    follower bigint NOT NULL,
    followee bigint NOT NULL,
    "time" timestamp without time zone
);


ALTER TABLE public.follows OWNER TO waterfall_user;

--
-- TOC entry 216 (class 1259 OID 16723)
-- Name: follows_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.follows_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.follows_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3363 (class 0 OID 0)
-- Dependencies: 216
-- Name: follows_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.follows_id_seq OWNED BY public.follows.id;


--
-- TOC entry 217 (class 1259 OID 16725)
-- Name: geoip_blocks; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.geoip_blocks (
    id bigint NOT NULL,
    network cidr,
    geoname_id bigint,
    registered_country_geoname_id bigint,
    is_anonymous_proxy boolean,
    is_satellite_provider boolean,
    postal_code text,
    latitude double precision,
    longitude double precision,
    accuracy_radius integer,
    represented_country_geoname_id bigint
);


ALTER TABLE public.geoip_blocks OWNER TO waterfall_user;

--
-- TOC entry 218 (class 1259 OID 16731)
-- Name: geoip_blocks_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.geoip_blocks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.geoip_blocks_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3364 (class 0 OID 0)
-- Dependencies: 218
-- Name: geoip_blocks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.geoip_blocks_id_seq OWNED BY public.geoip_blocks.id;


--
-- TOC entry 219 (class 1259 OID 16733)
-- Name: geoip_cities_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.geoip_cities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    MAXVALUE 9999999999999999
    CACHE 1;


ALTER TABLE public.geoip_cities_id_seq OWNER TO waterfall_user;

--
-- TOC entry 220 (class 1259 OID 16735)
-- Name: geoip_cities; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.geoip_cities (
    id bigint DEFAULT nextval('public.geoip_cities_id_seq'::regclass) NOT NULL,
    geoname_id bigint,
    locale_code text,
    continent_code text,
    continent_name text,
    country_iso_code text,
    country_name text,
    subdivision_1_iso_code text,
    subdivision_1_name text,
    subdivision_2_iso_code text,
    subdivision_2_name text,
    city_name text,
    metro_code text,
    time_zone text,
    is_in_european_union boolean
);


ALTER TABLE public.geoip_cities OWNER TO waterfall_user;

--
-- TOC entry 221 (class 1259 OID 16742)
-- Name: images; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.images (
    id bigint NOT NULL,
    paths jsonb,
    is_art boolean,
    servers integer[],
    caption text,
    accessibility_caption text,
    height integer,
    width integer,
    version integer DEFAULT 1
);


ALTER TABLE public.images OWNER TO waterfall_user;

--
-- TOC entry 222 (class 1259 OID 16748)
-- Name: images_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.images_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3365 (class 0 OID 0)
-- Dependencies: 222
-- Name: images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.images_id_seq OWNED BY public.images.id;


--
-- TOC entry 223 (class 1259 OID 16750)
-- Name: invites; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.invites (
    id bigint NOT NULL,
    code text,
    for_blog bigint,
    uses integer,
    name text,
    promotional boolean
);


ALTER TABLE public.invites OWNER TO waterfall_user;

--
-- TOC entry 224 (class 1259 OID 16756)
-- Name: invites_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.invites_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.invites_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3366 (class 0 OID 0)
-- Dependencies: 224
-- Name: invites_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.invites_id_seq OWNED BY public.invites.id;


--
-- TOC entry 225 (class 1259 OID 16758)
-- Name: ip_bans; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.ip_bans (
    id bigint NOT NULL,
    ip_address cidr,
    notes text
);


ALTER TABLE public.ip_bans OWNER TO waterfall_user;

--
-- TOC entry 226 (class 1259 OID 16764)
-- Name: ip_bans_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.ip_bans_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ip_bans_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3367 (class 0 OID 0)
-- Dependencies: 226
-- Name: ip_bans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.ip_bans_id_seq OWNED BY public.ip_bans.id;


--
-- TOC entry 227 (class 1259 OID 16766)
-- Name: likes_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.likes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.likes_id_seq OWNER TO waterfall_user;

--
-- TOC entry 228 (class 1259 OID 16768)
-- Name: likes; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.likes (
    id bigint DEFAULT nextval('public.likes_id_seq'::regclass) NOT NULL,
    blog_id bigint NOT NULL,
    source_post bigint NOT NULL,
    post_id bigint NOT NULL,
    "timestamp" timestamp without time zone
);


ALTER TABLE public.likes OWNER TO waterfall_user;

--
-- TOC entry 229 (class 1259 OID 16772)
-- Name: messages; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.messages (
    id bigint NOT NULL,
    sender bigint,
    recipient bigint,
    message text,
    anon boolean,
    answered boolean DEFAULT false,
    can_answer boolean,
    deleted_inbox boolean DEFAULT false,
    deleted_outbox boolean DEFAULT false,
    "timestamp" timestamp without time zone,
    message_type public.message_type
);


ALTER TABLE public.messages OWNER TO waterfall_user;

--
-- TOC entry 230 (class 1259 OID 16781)
-- Name: messages_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.messages_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3368 (class 0 OID 0)
-- Dependencies: 230
-- Name: messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.messages_id_seq OWNED BY public.messages.id;


--
-- TOC entry 231 (class 1259 OID 16783)
-- Name: notes; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.notes (
    id bigint NOT NULL,
    note_type public.notification_type,
    recipient bigint,
    actioner bigint,
    hide boolean,
    comment text,
    "timestamp" timestamp without time zone,
    post_id bigint,
    source_post bigint
);


ALTER TABLE public.notes OWNER TO waterfall_user;

--
-- TOC entry 232 (class 1259 OID 16789)
-- Name: notes_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.notes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.notes_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3369 (class 0 OID 0)
-- Dependencies: 232
-- Name: notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.notes_id_seq OWNED BY public.notes.id;


--
-- TOC entry 233 (class 1259 OID 16791)
-- Name: pages; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.pages (
    id bigint NOT NULL,
    on_blog bigint,
    url text,
    page_name text,
    page_title text,
    page_content text,
    show_in_nav boolean,
    page_type public.page_type,
    inline_images bigint[]
);


ALTER TABLE public.pages OWNER TO waterfall_user;

--
-- TOC entry 234 (class 1259 OID 16797)
-- Name: pages_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.pages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pages_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3370 (class 0 OID 0)
-- Dependencies: 234
-- Name: pages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.pages_id_seq OWNED BY public.pages.id;


--
-- TOC entry 235 (class 1259 OID 16799)
-- Name: polls; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.polls (
    id bigint NOT NULL,
    on_blog bigint,
    poll_question text,
    options text[],
    deadline timestamp without time zone,
    vote_type public.poll_type DEFAULT 'single'::public.poll_type
);


ALTER TABLE public.polls OWNER TO waterfall_user;

--
-- TOC entry 236 (class 1259 OID 16806)
-- Name: polls_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.polls_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.polls_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3371 (class 0 OID 0)
-- Dependencies: 236
-- Name: polls_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.polls_id_seq OWNED BY public.polls.id;


--
-- TOC entry 237 (class 1259 OID 16808)
-- Name: posts; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.posts (
    id bigint NOT NULL,
    post_type public.post_type NOT NULL,
    post_content text,
    post_title text,
    on_blog bigint,
    is_reblog boolean DEFAULT false,
    tags bigint[],
    source_post bigint,
    reblogged_from bigint DEFAULT 0,
    last_in_chain bigint DEFAULT 0,
    message_id bigint DEFAULT 0,
    image_id bigint[] DEFAULT '{0}'::bigint[],
    video_id bigint DEFAULT 0,
    audio_id bigint DEFAULT 0,
    poll_id bigint DEFAULT 0,
    link_data jsonb DEFAULT '[]'::jsonb,
    quote_data jsonb DEFAULT '[]'::jsonb,
    edited time without time zone,
    "timestamp" timestamp(6) without time zone,
    inline_images bigint[],
    post_status public.post_status,
    is_embed boolean DEFAULT false,
    embed_link text
);


ALTER TABLE public.posts OWNER TO waterfall_user;

--
-- TOC entry 238 (class 1259 OID 16825)
-- Name: posts_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.posts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.posts_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3372 (class 0 OID 0)
-- Dependencies: 238
-- Name: posts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.posts_id_seq OWNED BY public.posts.id;


--
-- TOC entry 239 (class 1259 OID 16827)
-- Name: raven_servers; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.raven_servers (
    id integer NOT NULL,
    server_name text,
    server_ip inet,
    server_port integer,
    server_role public.raven_role,
    storage_available bigint,
    hardware_score integer,
    verify_key text,
    storage_total bigint,
    last_heartbeat timestamp without time zone,
    server_status public.raven_status,
    lan_ip inet
);


ALTER TABLE public.raven_servers OWNER TO waterfall_user;

--
-- TOC entry 240 (class 1259 OID 16833)
-- Name: raven_servers_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.raven_servers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.raven_servers_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3373 (class 0 OID 0)
-- Dependencies: 240
-- Name: raven_servers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.raven_servers_id_seq OWNED BY public.raven_servers.id;


--
-- TOC entry 241 (class 1259 OID 16835)
-- Name: serials; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.serials (
    id bigint NOT NULL,
    key text,
    entitlements integer[],
    generated timestamp without time zone,
    used_at timestamp without time zone,
    used_by bigint,
    valid boolean
);


ALTER TABLE public.serials OWNER TO waterfall_user;

--
-- TOC entry 242 (class 1259 OID 16841)
-- Name: serials_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.serials_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.serials_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3374 (class 0 OID 0)
-- Dependencies: 242
-- Name: serials_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.serials_id_seq OWNED BY public.serials.id;


--
-- TOC entry 243 (class 1259 OID 16843)
-- Name: tags; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.tags (
    id bigint NOT NULL,
    tag text,
    lowercased text
);


ALTER TABLE public.tags OWNER TO waterfall_user;

--
-- TOC entry 244 (class 1259 OID 16849)
-- Name: tags_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.tags_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tags_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3375 (class 0 OID 0)
-- Dependencies: 244
-- Name: tags_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.tags_id_seq OWNED BY public.tags.id;


--
-- TOC entry 245 (class 1259 OID 16851)
-- Name: themes; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.themes (
    id bigint NOT NULL,
    name text,
    path text,
    default_theme boolean DEFAULT false
);


ALTER TABLE public.themes OWNER TO waterfall_user;

--
-- TOC entry 246 (class 1259 OID 16858)
-- Name: themes_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.themes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.themes_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3376 (class 0 OID 0)
-- Dependencies: 246
-- Name: themes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.themes_id_seq OWNED BY public.themes.id;


--
-- TOC entry 247 (class 1259 OID 16860)
-- Name: users; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    email text,
    password text,
    main_blog bigint,
    timezone text,
    customer_id text,
    uuid uuid,
    secret_key text,
    subscription_tier public.subscription_tiers DEFAULT 'free'::public.subscription_tiers,
    settings jsonb,
    verified boolean,
    dashboard_theme integer,
    blocked_users bigint[] DEFAULT '{}'::bigint[],
    blog_slots integer,
    date_of_birth date,
    last_ip inet,
    mod_level integer,
    badges_allowed integer[] DEFAULT '{}'::integer[],
    themes_allowed integer[] DEFAULT '{}'::integer[],
    staff_notes jsonb[],
    verify_key text,
    inbox_last_read timestamp without time zone,
    subscription_ends timestamp without time zone,
    registered_at timestamp without time zone,
    tag_blacklist text[],
    last_visit timestamp without time zone,
    account_type public.account_types,
    restrictions public.account_restrictions[] DEFAULT '{}'::public.account_restrictions[],
    flags public.account_flags[] DEFAULT '{}'::public.account_flags[],
    pronouns text
);


ALTER TABLE public.users OWNER TO waterfall_user;

--
-- TOC entry 248 (class 1259 OID 16872)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3377 (class 0 OID 0)
-- Dependencies: 248
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 249 (class 1259 OID 16874)
-- Name: video; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.video (
    id bigint NOT NULL,
    paths jsonb,
    servers integer[],
    transcode_status public.transcode_status
);


ALTER TABLE public.video OWNER TO waterfall_user;

--
-- TOC entry 250 (class 1259 OID 16880)
-- Name: video_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.video_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.video_id_seq OWNER TO waterfall_user;

--
-- TOC entry 3378 (class 0 OID 0)
-- Dependencies: 250
-- Name: video_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: waterfall_user
--

ALTER SEQUENCE public.video_id_seq OWNED BY public.video.id;


--
-- TOC entry 251 (class 1259 OID 16882)
-- Name: votes_id_seq; Type: SEQUENCE; Schema: public; Owner: waterfall_user
--

CREATE SEQUENCE public.votes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.votes_id_seq OWNER TO waterfall_user;

--
-- TOC entry 252 (class 1259 OID 16884)
-- Name: votes; Type: TABLE; Schema: public; Owner: waterfall_user
--

CREATE TABLE public.votes (
    id bigint DEFAULT nextval('public.votes_id_seq'::regclass) NOT NULL,
    from_user bigint NOT NULL,
    on_poll bigint NOT NULL,
    "time" timestamp without time zone,
    option integer
);


ALTER TABLE public.votes OWNER TO waterfall_user;

--
-- TOC entry 3062 (class 2604 OID 16888)
-- Name: analytics id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.analytics ALTER COLUMN id SET DEFAULT nextval('public.analytics_id_seq'::regclass);


--
-- TOC entry 3064 (class 2604 OID 16889)
-- Name: audio id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.audio ALTER COLUMN id SET DEFAULT nextval('public.audio_id_seq'::regclass);


--
-- TOC entry 3069 (class 2604 OID 16890)
-- Name: badges id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.badges ALTER COLUMN id SET DEFAULT nextval('public.badges_id_seq'::regclass);


--
-- TOC entry 3072 (class 2604 OID 16891)
-- Name: blogs id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.blogs ALTER COLUMN id SET DEFAULT nextval('public.blogs_id_seq'::regclass);


--
-- TOC entry 3075 (class 2604 OID 16893)
-- Name: featured_posts id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.featured_posts ALTER COLUMN id SET DEFAULT nextval('public.featured_posts_id_seq'::regclass);


--
-- TOC entry 3076 (class 2604 OID 16894)
-- Name: follows id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.follows ALTER COLUMN id SET DEFAULT nextval('public.follows_id_seq'::regclass);


--
-- TOC entry 3077 (class 2604 OID 16895)
-- Name: geoip_blocks id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.geoip_blocks ALTER COLUMN id SET DEFAULT nextval('public.geoip_blocks_id_seq'::regclass);


--
-- TOC entry 3079 (class 2604 OID 16896)
-- Name: images id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.images ALTER COLUMN id SET DEFAULT nextval('public.images_id_seq'::regclass);


--
-- TOC entry 3081 (class 2604 OID 16897)
-- Name: invites id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.invites ALTER COLUMN id SET DEFAULT nextval('public.invites_id_seq'::regclass);


--
-- TOC entry 3082 (class 2604 OID 16898)
-- Name: ip_bans id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.ip_bans ALTER COLUMN id SET DEFAULT nextval('public.ip_bans_id_seq'::regclass);


--
-- TOC entry 3087 (class 2604 OID 16899)
-- Name: messages id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.messages ALTER COLUMN id SET DEFAULT nextval('public.messages_id_seq'::regclass);


--
-- TOC entry 3088 (class 2604 OID 16900)
-- Name: notes id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.notes ALTER COLUMN id SET DEFAULT nextval('public.notes_id_seq'::regclass);


--
-- TOC entry 3089 (class 2604 OID 16901)
-- Name: pages id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.pages ALTER COLUMN id SET DEFAULT nextval('public.pages_id_seq'::regclass);


--
-- TOC entry 3090 (class 2604 OID 16902)
-- Name: polls id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.polls ALTER COLUMN id SET DEFAULT nextval('public.polls_id_seq'::regclass);


--
-- TOC entry 3103 (class 2604 OID 16903)
-- Name: posts id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.posts ALTER COLUMN id SET DEFAULT nextval('public.posts_id_seq'::regclass);


--
-- TOC entry 3104 (class 2604 OID 16904)
-- Name: raven_servers id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.raven_servers ALTER COLUMN id SET DEFAULT nextval('public.raven_servers_id_seq'::regclass);


--
-- TOC entry 3105 (class 2604 OID 16905)
-- Name: serials id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.serials ALTER COLUMN id SET DEFAULT nextval('public.serials_id_seq'::regclass);


--
-- TOC entry 3106 (class 2604 OID 16906)
-- Name: tags id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.tags ALTER COLUMN id SET DEFAULT nextval('public.tags_id_seq'::regclass);


--
-- TOC entry 3107 (class 2604 OID 16907)
-- Name: themes id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.themes ALTER COLUMN id SET DEFAULT nextval('public.themes_id_seq'::regclass);


--
-- TOC entry 3115 (class 2604 OID 16908)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 3116 (class 2604 OID 16909)
-- Name: video id; Type: DEFAULT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.video ALTER COLUMN id SET DEFAULT nextval('public.video_id_seq'::regclass);


--
-- TOC entry 3119 (class 2606 OID 16911)
-- Name: analytics analytics_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.analytics
    ADD CONSTRAINT analytics_pkey PRIMARY KEY (id);


--
-- TOC entry 3123 (class 2606 OID 16913)
-- Name: art_data art_data_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.art_data
    ADD CONSTRAINT art_data_pkey PRIMARY KEY (id);


--
-- TOC entry 3127 (class 2606 OID 16917)
-- Name: audio audio_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.audio
    ADD CONSTRAINT audio_pkey PRIMARY KEY (id);


--
-- TOC entry 3130 (class 2606 OID 16919)
-- Name: badges badges_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.badges
    ADD CONSTRAINT badges_pkey PRIMARY KEY (id);


--
-- TOC entry 3132 (class 2606 OID 16921)
-- Name: blog_members blog_members_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.blog_members
    ADD CONSTRAINT blog_members_pkey PRIMARY KEY (id);


--
-- TOC entry 3140 (class 2606 OID 16923)
-- Name: blogs blogs_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.blogs
    ADD CONSTRAINT blogs_pkey PRIMARY KEY (id);


--
-- TOC entry 3142 (class 2606 OID 16929)
-- Name: entitlements entitlements_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.entitlements
    ADD CONSTRAINT entitlements_pkey PRIMARY KEY (id);


--
-- TOC entry 3144 (class 2606 OID 16931)
-- Name: featured_posts featured_posts_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.featured_posts
    ADD CONSTRAINT featured_posts_pkey PRIMARY KEY (id);


--
-- TOC entry 3148 (class 2606 OID 16933)
-- Name: follows follows_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.follows
    ADD CONSTRAINT follows_pkey PRIMARY KEY (id);


--
-- TOC entry 3153 (class 2606 OID 16935)
-- Name: geoip_cities geoip_cities_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.geoip_cities
    ADD CONSTRAINT geoip_cities_pkey PRIMARY KEY (id);


--
-- TOC entry 3155 (class 2606 OID 16937)
-- Name: images images_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.images
    ADD CONSTRAINT images_pkey PRIMARY KEY (id);


--
-- TOC entry 3160 (class 2606 OID 16939)
-- Name: invites invites_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.invites
    ADD CONSTRAINT invites_pkey PRIMARY KEY (id);


--
-- TOC entry 3162 (class 2606 OID 16941)
-- Name: ip_bans ip_bans_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.ip_bans
    ADD CONSTRAINT ip_bans_pkey PRIMARY KEY (id);


--
-- TOC entry 3165 (class 2606 OID 16943)
-- Name: likes likes_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.likes
    ADD CONSTRAINT likes_pkey PRIMARY KEY (id);


--
-- TOC entry 3175 (class 2606 OID 16945)
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id);


--
-- TOC entry 3184 (class 2606 OID 16947)
-- Name: notes notes_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.notes
    ADD CONSTRAINT notes_pkey PRIMARY KEY (id);


--
-- TOC entry 3189 (class 2606 OID 16949)
-- Name: pages pages_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.pages
    ADD CONSTRAINT pages_pkey PRIMARY KEY (id);


--
-- TOC entry 3193 (class 2606 OID 16951)
-- Name: polls polls_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.polls
    ADD CONSTRAINT polls_pkey PRIMARY KEY (id);


--
-- TOC entry 3201 (class 2606 OID 16953)
-- Name: posts posts_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_pkey PRIMARY KEY (id);


--
-- TOC entry 3203 (class 2606 OID 16955)
-- Name: serials serials_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.serials
    ADD CONSTRAINT serials_pkey PRIMARY KEY (id);


--
-- TOC entry 3208 (class 2606 OID 16957)
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id);


--
-- TOC entry 3210 (class 2606 OID 16959)
-- Name: themes themes_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.themes
    ADD CONSTRAINT themes_pkey PRIMARY KEY (id);


--
-- TOC entry 3214 (class 2606 OID 16961)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 3216 (class 2606 OID 16963)
-- Name: video video_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.video
    ADD CONSTRAINT video_pkey PRIMARY KEY (id);


--
-- TOC entry 3221 (class 2606 OID 16965)
-- Name: votes votes_pkey; Type: CONSTRAINT; Schema: public; Owner: waterfall_user
--

ALTER TABLE ONLY public.votes
    ADD CONSTRAINT votes_pkey PRIMARY KEY (id);


--
-- TOC entry 3120 (class 1259 OID 16966)
-- Name: art_data_image_id; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX art_data_image_id ON public.art_data USING btree (image_id);


--
-- TOC entry 3121 (class 1259 OID 16967)
-- Name: art_data_image_md5; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX art_data_image_md5 ON public.art_data USING btree (image_md5);


--
-- TOC entry 3124 (class 1259 OID 16968)
-- Name: art_data_post_id; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX art_data_post_id ON public.art_data USING btree (post_id);


--
-- TOC entry 3125 (class 1259 OID 16972)
-- Name: audio_md5; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX audio_md5 ON public.audio USING btree (md5);


--
-- TOC entry 3128 (class 1259 OID 16973)
-- Name: audio_servers; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX audio_servers ON public.audio USING btree (servers);


--
-- TOC entry 3133 (class 1259 OID 16974)
-- Name: blogmem_blog_id; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX blogmem_blog_id ON public.blog_members USING btree (blog_id);


--
-- TOC entry 3134 (class 1259 OID 16975)
-- Name: blogmem_user_id; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX blogmem_user_id ON public.blog_members USING btree (user_id);


--
-- TOC entry 3135 (class 1259 OID 16976)
-- Name: blogmembers_confirmed; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX blogmembers_confirmed ON public.blog_members USING btree (confirmed);


--
-- TOC entry 3136 (class 1259 OID 16977)
-- Name: blogs_allow_search; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX blogs_allow_search ON public.blogs USING btree (allow_search);


--
-- TOC entry 3137 (class 1259 OID 16978)
-- Name: blogs_blog_name; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX blogs_blog_name ON public.blogs USING btree (blog_name varchar_ops);


--
-- TOC entry 3138 (class 1259 OID 16979)
-- Name: blogs_owner_id; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX blogs_owner_id ON public.blogs USING btree (owner_id);


--
-- TOC entry 3145 (class 1259 OID 16984)
-- Name: follows_followee; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX follows_followee ON public.follows USING btree (followee);


--
-- TOC entry 3146 (class 1259 OID 16985)
-- Name: follows_follower; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX follows_follower ON public.follows USING btree (follower);


--
-- TOC entry 3149 (class 1259 OID 16986)
-- Name: follows_unique; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE UNIQUE INDEX follows_unique ON public.follows USING btree (follower, followee);


--
-- TOC entry 3151 (class 1259 OID 16987)
-- Name: geoip_cities_geonameid; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX geoip_cities_geonameid ON public.geoip_cities USING btree (geoname_id);


--
-- TOC entry 3150 (class 1259 OID 16988)
-- Name: geoip_network; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX geoip_network ON public.geoip_blocks USING btree (network);


--
-- TOC entry 3156 (class 1259 OID 16989)
-- Name: images_servers; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX images_servers ON public.images USING btree (servers);


--
-- TOC entry 3157 (class 1259 OID 16990)
-- Name: invites_code; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX invites_code ON public.invites USING btree (code);


--
-- TOC entry 3158 (class 1259 OID 16991)
-- Name: invites_for_blog; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX invites_for_blog ON public.invites USING btree (for_blog);


--
-- TOC entry 3163 (class 1259 OID 16992)
-- Name: likes_blog_id; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX likes_blog_id ON public.likes USING btree (blog_id);


--
-- TOC entry 3166 (class 1259 OID 16993)
-- Name: likes_post_id; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX likes_post_id ON public.likes USING btree (post_id);


--
-- TOC entry 3167 (class 1259 OID 16994)
-- Name: likes_source_post; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX likes_source_post ON public.likes USING btree (source_post);


--
-- TOC entry 3168 (class 1259 OID 16995)
-- Name: likes_timestamp; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX likes_timestamp ON public.likes USING btree ("timestamp" DESC NULLS LAST);


--
-- TOC entry 3169 (class 1259 OID 16996)
-- Name: message_answered; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX message_answered ON public.messages USING btree (answered);


--
-- TOC entry 3170 (class 1259 OID 16997)
-- Name: message_indeleted; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX message_indeleted ON public.messages USING btree (deleted_inbox);


--
-- TOC entry 3171 (class 1259 OID 16998)
-- Name: message_outdeleted; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX message_outdeleted ON public.messages USING btree (deleted_outbox);


--
-- TOC entry 3172 (class 1259 OID 16999)
-- Name: message_recipient; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX message_recipient ON public.messages USING btree (recipient);


--
-- TOC entry 3173 (class 1259 OID 17000)
-- Name: message_sender; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX message_sender ON public.messages USING btree (sender);


--
-- TOC entry 3176 (class 1259 OID 17001)
-- Name: messages_timestamp; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX messages_timestamp ON public.messages USING btree ("timestamp" DESC NULLS LAST);


--
-- TOC entry 3177 (class 1259 OID 17002)
-- Name: note_actioner; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX note_actioner ON public.notes USING btree (actioner);


--
-- TOC entry 3178 (class 1259 OID 17003)
-- Name: note_hide; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX note_hide ON public.notes USING btree (hide);


--
-- TOC entry 3179 (class 1259 OID 17004)
-- Name: note_post_id; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX note_post_id ON public.notes USING btree (post_id);


--
-- TOC entry 3180 (class 1259 OID 17005)
-- Name: note_recipient; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX note_recipient ON public.notes USING btree (recipient);


--
-- TOC entry 3181 (class 1259 OID 17006)
-- Name: note_source_post; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX note_source_post ON public.notes USING btree (source_post);


--
-- TOC entry 3182 (class 1259 OID 17007)
-- Name: note_timestamp; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX note_timestamp ON public.notes USING btree ("timestamp" DESC NULLS LAST);


--
-- TOC entry 3185 (class 1259 OID 17008)
-- Name: page_inline_images; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX page_inline_images ON public.pages USING btree (inline_images);


--
-- TOC entry 3186 (class 1259 OID 17009)
-- Name: pages_on_blog; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX pages_on_blog ON public.pages USING btree (on_blog);


--
-- TOC entry 3187 (class 1259 OID 17010)
-- Name: pages_onblog_url_unique; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE UNIQUE INDEX pages_onblog_url_unique ON public.pages USING btree (on_blog, url);


--
-- TOC entry 3190 (class 1259 OID 17011)
-- Name: pages_url; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX pages_url ON public.pages USING btree (url);


--
-- TOC entry 3191 (class 1259 OID 17012)
-- Name: poll_on_blog; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX poll_on_blog ON public.polls USING btree (on_blog);


--
-- TOC entry 3194 (class 1259 OID 17013)
-- Name: post_is_reblog; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX post_is_reblog ON public.posts USING btree (is_reblog);


--
-- TOC entry 3195 (class 1259 OID 17014)
-- Name: post_on_blog; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX post_on_blog ON public.posts USING btree (on_blog);


--
-- TOC entry 3196 (class 1259 OID 22175)
-- Name: post_post_status; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX post_post_status ON public.posts USING btree (post_status);


--
-- TOC entry 3197 (class 1259 OID 17015)
-- Name: post_post_type; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX post_post_type ON public.posts USING btree (post_type);


--
-- TOC entry 3198 (class 1259 OID 17016)
-- Name: post_source_post; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX post_source_post ON public.posts USING btree (source_post);


--
-- TOC entry 3199 (class 1259 OID 17017)
-- Name: post_timestamp; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX post_timestamp ON public.posts USING btree ("timestamp" DESC NULLS LAST);


--
-- TOC entry 3204 (class 1259 OID 17018)
-- Name: tag_lower; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX tag_lower ON public.tags USING hash (lowercased varchar_ops);


--
-- TOC entry 3205 (class 1259 OID 17019)
-- Name: tag_tag; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX tag_tag ON public.tags USING hash (tag varchar_ops);


--
-- TOC entry 3206 (class 1259 OID 17020)
-- Name: tags_nsfw_lower_check; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX tags_nsfw_lower_check ON public.tags USING btree (lowercased) WHERE ("left"(lowercased, 4) = 'nsfw'::text);


--
-- TOC entry 3211 (class 1259 OID 17021)
-- Name: user_email; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX user_email ON public.users USING hash (email varchar_ops);


--
-- TOC entry 3212 (class 1259 OID 17022)
-- Name: user_main_blog; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX user_main_blog ON public.users USING btree (main_blog);


--
-- TOC entry 3217 (class 1259 OID 17023)
-- Name: video_servers; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX video_servers ON public.video USING btree (servers);


--
-- TOC entry 3218 (class 1259 OID 17024)
-- Name: votes_from_user; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX votes_from_user ON public.votes USING btree (from_user);


--
-- TOC entry 3219 (class 1259 OID 17025)
-- Name: votes_on_poll; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX votes_on_poll ON public.votes USING btree (on_poll);


--
-- TOC entry 3222 (class 1259 OID 17026)
-- Name: votes_unique; Type: INDEX; Schema: public; Owner: waterfall_user
--

CREATE INDEX votes_unique ON public.votes USING btree (from_user, on_poll);


-- Completed on 2021-03-03 07:25:01

--
-- PostgreSQL database dump complete
--

