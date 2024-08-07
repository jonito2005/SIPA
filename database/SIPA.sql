PGDMP  %    :                |            SIPA    16.3    16.3 7    "           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                      false            #           0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                      false            $           0    0 
   SEARCHPATH 
   SEARCHPATH     8   SELECT pg_catalog.set_config('search_path', '', false);
                      false            %           1262    16859    SIPA    DATABASE     }   CREATE DATABASE "SIPA" WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'English_Indonesia.1252';
    DROP DATABASE "SIPA";
                postgres    false                        2615    2200    public    SCHEMA        CREATE SCHEMA public;
    DROP SCHEMA public;
                pg_database_owner    false            &           0    0    SCHEMA public    COMMENT     6   COMMENT ON SCHEMA public IS 'standard public schema';
                   pg_database_owner    false    4            �            1255    16860    check_khs()    FUNCTION     �  CREATE FUNCTION public.check_khs() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM khs
        WHERE id_mahasiswa = NEW.id_mahasiswa
        AND semester = NEW.semester
        AND tahun_ajaran = NEW.tahun_ajaran
        AND id_khs <> NEW.id_khs -- Abaikan KHS yang sedang diupdate
    ) THEN
        RAISE EXCEPTION 'Mahasiswa sudah memiliki KHS untuk semester dan tahun ajaran ini';
    END IF;
    RETURN NEW;
END;
$$;
 "   DROP FUNCTION public.check_khs();
       public          postgres    false    4            �            1255    16861 #   hitung_bimbingan_mahasiswa(integer)    FUNCTION     !  CREATE FUNCTION public.hitung_bimbingan_mahasiswa(id_mhs integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    jumlah_bimbingan INTEGER;
BEGIN
    SELECT COUNT(*) INTO jumlah_bimbingan
    FROM bimbingan
    WHERE id_mahasiswa = id_mhs;
    RETURN jumlah_bimbingan;
END;
$$;
 A   DROP FUNCTION public.hitung_bimbingan_mahasiswa(id_mhs integer);
       public          postgres    false    4            �            1255    16862    log_login()    FUNCTION     ?  CREATE FUNCTION public.log_login() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Periksa apakah pengguna adalah mahasiswa
    IF EXISTS (SELECT 1 FROM mahasiswa WHERE id_pengguna = NEW.id_pengguna) THEN
        INSERT INTO LOG_PENGGUNA (id_pengguna, role)
        VALUES (NEW.id_pengguna, 'mahasiswa');
    -- Periksa apakah pengguna adalah dosen
    ELSIF EXISTS (SELECT 1 FROM dosen WHERE id_pengguna = NEW.id_pengguna) THEN
        INSERT INTO LOG_PENGGUNA (id_pengguna, role)
        VALUES (NEW.id_pengguna, 'dosen');
    END IF;

    RETURN NEW;
END;
$$;
 "   DROP FUNCTION public.log_login();
       public          postgres    false    4            �            1259    16863 	   bimbingan    TABLE     �   CREATE TABLE public.bimbingan (
    id_bimbingan integer NOT NULL,
    id_mahasiswa integer,
    id_dosen integer,
    tanggal_bimbingan date NOT NULL,
    catatan text
);
    DROP TABLE public.bimbingan;
       public         heap    postgres    false    4            �            1259    16868    bimbingan_id_bimbingan_seq    SEQUENCE     �   CREATE SEQUENCE public.bimbingan_id_bimbingan_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 1   DROP SEQUENCE public.bimbingan_id_bimbingan_seq;
       public          postgres    false    4    215            '           0    0    bimbingan_id_bimbingan_seq    SEQUENCE OWNED BY     Y   ALTER SEQUENCE public.bimbingan_id_bimbingan_seq OWNED BY public.bimbingan.id_bimbingan;
          public          postgres    false    216            �            1259    16869    dosen    TABLE     �   CREATE TABLE public.dosen (
    id_dosen integer NOT NULL,
    id_pengguna integer,
    nidn character varying(20) NOT NULL,
    alamat_dosen character varying(100)
);
    DROP TABLE public.dosen;
       public         heap    postgres    false    4            �            1259    16872    dosen_id_dosen_seq    SEQUENCE     �   CREATE SEQUENCE public.dosen_id_dosen_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 )   DROP SEQUENCE public.dosen_id_dosen_seq;
       public          postgres    false    4    217            (           0    0    dosen_id_dosen_seq    SEQUENCE OWNED BY     I   ALTER SEQUENCE public.dosen_id_dosen_seq OWNED BY public.dosen.id_dosen;
          public          postgres    false    218            �            1259    16873    khs    TABLE     �   CREATE TABLE public.khs (
    id_khs integer NOT NULL,
    id_mahasiswa integer,
    semester integer NOT NULL,
    tahun_ajaran character varying(100) NOT NULL,
    file_khs text NOT NULL,
    ipk integer
);
    DROP TABLE public.khs;
       public         heap    postgres    false    4            �            1259    16878    khs_id_khs_seq    SEQUENCE     �   CREATE SEQUENCE public.khs_id_khs_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 %   DROP SEQUENCE public.khs_id_khs_seq;
       public          postgres    false    219    4            )           0    0    khs_id_khs_seq    SEQUENCE OWNED BY     A   ALTER SEQUENCE public.khs_id_khs_seq OWNED BY public.khs.id_khs;
          public          postgres    false    220            �            1259    16892 	   mahasiswa    TABLE     �   CREATE TABLE public.mahasiswa (
    id_mahasiswa integer NOT NULL,
    id_pengguna integer,
    nim character varying(50) NOT NULL,
    program_studi character varying(100) NOT NULL,
    alamat_mahasiswa character varying(100),
    tahun_lulus integer
);
    DROP TABLE public.mahasiswa;
       public         heap    postgres    false    4            �            1259    16895    mahasiswa_id_mahasiswa_seq    SEQUENCE     �   CREATE SEQUENCE public.mahasiswa_id_mahasiswa_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 1   DROP SEQUENCE public.mahasiswa_id_mahasiswa_seq;
       public          postgres    false    221    4            *           0    0    mahasiswa_id_mahasiswa_seq    SEQUENCE OWNED BY     Y   ALTER SEQUENCE public.mahasiswa_id_mahasiswa_seq OWNED BY public.mahasiswa.id_mahasiswa;
          public          postgres    false    222            �            1259    16896    pengguna    TABLE     �  CREATE TABLE public.pengguna (
    id_pengguna integer NOT NULL,
    nama_pengguna character varying(255) NOT NULL,
    email_pengguna character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    status character varying(10) NOT NULL,
    nomor_hp bigint,
    foto_profil character varying(255),
    CONSTRAINT pengguna_role_check CHECK (((status)::text = ANY (ARRAY[('dosen'::character varying)::text, ('mahasiswa'::character varying)::text])))
);
    DROP TABLE public.pengguna;
       public         heap    postgres    false    4            �            1259    16902    pengguna_id_pengguna_seq    SEQUENCE     �   CREATE SEQUENCE public.pengguna_id_pengguna_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 /   DROP SEQUENCE public.pengguna_id_pengguna_seq;
       public          postgres    false    4    223            +           0    0    pengguna_id_pengguna_seq    SEQUENCE OWNED BY     U   ALTER SEQUENCE public.pengguna_id_pengguna_seq OWNED BY public.pengguna.id_pengguna;
          public          postgres    false    224            g           2604    16903    bimbingan id_bimbingan    DEFAULT     �   ALTER TABLE ONLY public.bimbingan ALTER COLUMN id_bimbingan SET DEFAULT nextval('public.bimbingan_id_bimbingan_seq'::regclass);
 E   ALTER TABLE public.bimbingan ALTER COLUMN id_bimbingan DROP DEFAULT;
       public          postgres    false    216    215            h           2604    16904    dosen id_dosen    DEFAULT     p   ALTER TABLE ONLY public.dosen ALTER COLUMN id_dosen SET DEFAULT nextval('public.dosen_id_dosen_seq'::regclass);
 =   ALTER TABLE public.dosen ALTER COLUMN id_dosen DROP DEFAULT;
       public          postgres    false    218    217            i           2604    16905 
   khs id_khs    DEFAULT     h   ALTER TABLE ONLY public.khs ALTER COLUMN id_khs SET DEFAULT nextval('public.khs_id_khs_seq'::regclass);
 9   ALTER TABLE public.khs ALTER COLUMN id_khs DROP DEFAULT;
       public          postgres    false    220    219            j           2604    16908    mahasiswa id_mahasiswa    DEFAULT     �   ALTER TABLE ONLY public.mahasiswa ALTER COLUMN id_mahasiswa SET DEFAULT nextval('public.mahasiswa_id_mahasiswa_seq'::regclass);
 E   ALTER TABLE public.mahasiswa ALTER COLUMN id_mahasiswa DROP DEFAULT;
       public          postgres    false    222    221            k           2604    16909    pengguna id_pengguna    DEFAULT     |   ALTER TABLE ONLY public.pengguna ALTER COLUMN id_pengguna SET DEFAULT nextval('public.pengguna_id_pengguna_seq'::regclass);
 C   ALTER TABLE public.pengguna ALTER COLUMN id_pengguna DROP DEFAULT;
       public          postgres    false    224    223                      0    16863 	   bimbingan 
   TABLE DATA           e   COPY public.bimbingan (id_bimbingan, id_mahasiswa, id_dosen, tanggal_bimbingan, catatan) FROM stdin;
    public          postgres    false    215   �D                 0    16869    dosen 
   TABLE DATA           J   COPY public.dosen (id_dosen, id_pengguna, nidn, alamat_dosen) FROM stdin;
    public          postgres    false    217   gF                 0    16873    khs 
   TABLE DATA           Z   COPY public.khs (id_khs, id_mahasiswa, semester, tahun_ajaran, file_khs, ipk) FROM stdin;
    public          postgres    false    219   �F                 0    16892 	   mahasiswa 
   TABLE DATA           q   COPY public.mahasiswa (id_mahasiswa, id_pengguna, nim, program_studi, alamat_mahasiswa, tahun_lulus) FROM stdin;
    public          postgres    false    221   7G                 0    16896    pengguna 
   TABLE DATA           w   COPY public.pengguna (id_pengguna, nama_pengguna, email_pengguna, password, status, nomor_hp, foto_profil) FROM stdin;
    public          postgres    false    223   �G       ,           0    0    bimbingan_id_bimbingan_seq    SEQUENCE SET     I   SELECT pg_catalog.setval('public.bimbingan_id_bimbingan_seq', 23, true);
          public          postgres    false    216            -           0    0    dosen_id_dosen_seq    SEQUENCE SET     A   SELECT pg_catalog.setval('public.dosen_id_dosen_seq', 1, false);
          public          postgres    false    218            .           0    0    khs_id_khs_seq    SEQUENCE SET     =   SELECT pg_catalog.setval('public.khs_id_khs_seq', 1, false);
          public          postgres    false    220            /           0    0    mahasiswa_id_mahasiswa_seq    SEQUENCE SET     H   SELECT pg_catalog.setval('public.mahasiswa_id_mahasiswa_seq', 3, true);
          public          postgres    false    222            0           0    0    pengguna_id_pengguna_seq    SEQUENCE SET     G   SELECT pg_catalog.setval('public.pengguna_id_pengguna_seq', 34, true);
          public          postgres    false    224            n           2606    16911    bimbingan bimbingan_pkey 
   CONSTRAINT     `   ALTER TABLE ONLY public.bimbingan
    ADD CONSTRAINT bimbingan_pkey PRIMARY KEY (id_bimbingan);
 B   ALTER TABLE ONLY public.bimbingan DROP CONSTRAINT bimbingan_pkey;
       public            postgres    false    215            p           2606    16913    dosen dosen_id_pengguna_key 
   CONSTRAINT     ]   ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_id_pengguna_key UNIQUE (id_pengguna);
 E   ALTER TABLE ONLY public.dosen DROP CONSTRAINT dosen_id_pengguna_key;
       public            postgres    false    217            r           2606    16915    dosen dosen_nidn_key 
   CONSTRAINT     O   ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_nidn_key UNIQUE (nidn);
 >   ALTER TABLE ONLY public.dosen DROP CONSTRAINT dosen_nidn_key;
       public            postgres    false    217            t           2606    16917    dosen dosen_pkey 
   CONSTRAINT     T   ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_pkey PRIMARY KEY (id_dosen);
 :   ALTER TABLE ONLY public.dosen DROP CONSTRAINT dosen_pkey;
       public            postgres    false    217            v           2606    16919    khs khs_pkey 
   CONSTRAINT     N   ALTER TABLE ONLY public.khs
    ADD CONSTRAINT khs_pkey PRIMARY KEY (id_khs);
 6   ALTER TABLE ONLY public.khs DROP CONSTRAINT khs_pkey;
       public            postgres    false    219            x           2606    16925 #   mahasiswa mahasiswa_id_pengguna_key 
   CONSTRAINT     e   ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_id_pengguna_key UNIQUE (id_pengguna);
 M   ALTER TABLE ONLY public.mahasiswa DROP CONSTRAINT mahasiswa_id_pengguna_key;
       public            postgres    false    221            z           2606    16927    mahasiswa mahasiswa_nim_key 
   CONSTRAINT     U   ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_nim_key UNIQUE (nim);
 E   ALTER TABLE ONLY public.mahasiswa DROP CONSTRAINT mahasiswa_nim_key;
       public            postgres    false    221            |           2606    16929    mahasiswa mahasiswa_pkey 
   CONSTRAINT     `   ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_pkey PRIMARY KEY (id_mahasiswa);
 B   ALTER TABLE ONLY public.mahasiswa DROP CONSTRAINT mahasiswa_pkey;
       public            postgres    false    221            ~           2606    16931 $   pengguna pengguna_email_pengguna_key 
   CONSTRAINT     i   ALTER TABLE ONLY public.pengguna
    ADD CONSTRAINT pengguna_email_pengguna_key UNIQUE (email_pengguna);
 N   ALTER TABLE ONLY public.pengguna DROP CONSTRAINT pengguna_email_pengguna_key;
       public            postgres    false    223            �           2606    16933    pengguna pengguna_pkey 
   CONSTRAINT     ]   ALTER TABLE ONLY public.pengguna
    ADD CONSTRAINT pengguna_pkey PRIMARY KEY (id_pengguna);
 @   ALTER TABLE ONLY public.pengguna DROP CONSTRAINT pengguna_pkey;
       public            postgres    false    223            �           2620    16934    khs khs_trigger    TRIGGER     s   CREATE TRIGGER khs_trigger BEFORE INSERT OR UPDATE ON public.khs FOR EACH ROW EXECUTE FUNCTION public.check_khs();
 (   DROP TRIGGER khs_trigger ON public.khs;
       public          postgres    false    219    225            �           2606    16936 !   bimbingan bimbingan_id_dosen_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.bimbingan
    ADD CONSTRAINT bimbingan_id_dosen_fkey FOREIGN KEY (id_dosen) REFERENCES public.dosen(id_dosen);
 K   ALTER TABLE ONLY public.bimbingan DROP CONSTRAINT bimbingan_id_dosen_fkey;
       public          postgres    false    217    4724    215            �           2606    16941 %   bimbingan bimbingan_id_mahasiswa_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.bimbingan
    ADD CONSTRAINT bimbingan_id_mahasiswa_fkey FOREIGN KEY (id_mahasiswa) REFERENCES public.mahasiswa(id_mahasiswa);
 O   ALTER TABLE ONLY public.bimbingan DROP CONSTRAINT bimbingan_id_mahasiswa_fkey;
       public          postgres    false    215    4732    221            �           2606    16946    dosen dosen_id_pengguna_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_id_pengguna_fkey FOREIGN KEY (id_pengguna) REFERENCES public.pengguna(id_pengguna);
 F   ALTER TABLE ONLY public.dosen DROP CONSTRAINT dosen_id_pengguna_fkey;
       public          postgres    false    223    4736    217            �           2606    16951    khs khs_id_mahasiswa_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.khs
    ADD CONSTRAINT khs_id_mahasiswa_fkey FOREIGN KEY (id_mahasiswa) REFERENCES public.mahasiswa(id_mahasiswa);
 C   ALTER TABLE ONLY public.khs DROP CONSTRAINT khs_id_mahasiswa_fkey;
       public          postgres    false    221    4732    219            �           2606    16966 $   mahasiswa mahasiswa_id_pengguna_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_id_pengguna_fkey FOREIGN KEY (id_pengguna) REFERENCES public.pengguna(id_pengguna);
 N   ALTER TABLE ONLY public.mahasiswa DROP CONSTRAINT mahasiswa_id_pengguna_fkey;
       public          postgres    false    221    223    4736               �  x����N�0E��W�%��]�X@TQQ�l�d�v�ʏV�=��
- Y�칞s�$QE�2�M3U�ɨ��T�]Q�!ۀ�p#=��:Y
U�l��$��C]f�_��t6bW_҅�[�s�6$�h��!h��7O�
<��K���A>�����v��@����`���rW��9]�`�M�zZ�>
I|��C� +��NV`t4��b�{Pm�Z����Z�ܜ�� ���D��G���X��ɠ��t�3�bv
�ޢt�������w��_c_v|1����Hm ���[..N�w�@-��;'.y�&R
_����YC��*���\�8��VMר���h~�]��������ˬ�B����>|.������m����-/�!^-�         ,   x�3�4�4426153������Sp*M�P�KLOT03����� �E�         �   x��̻�  ���/����lL�F��D"��m��(9��q(
��4�7��G}�>:jr�[�-��;6O�|�[�7�����1w����I8cD�V���*P5Z]���U����Ҷ�J�*���         �   x���A
� ��x�9Aq���6�Pȶ��"�6hR��+H����?���h�ܖR({�p����'�8��k��v�az�V򱢻Bke��������Q�I�N�)�ǱItJ�)�뭕��m�뢔�S\e         f  x����n�0�ϓ�ȁ�qǔd�D]6mW���z��$�$��o�&j�^��ƿl��y~k�J���ƃ�!Wgx�	DI`��T0,�)�Dn�s:	"&Y��	�;4����Ӎ¼.��.)���0Ņ�!C[��Uc_�;6��C���#HZ�m@^�+���i
��[�fPJ� ���B1�-�Q1�8�;�:�l䰦���C�YB�����E�Xݝ��*���U��]�?��o�4�d�U3��K ��F��m�����n������0�v۳<�w�o�֠��*��&E��%��Ѱ[�Ζ�J_��P�U�UV�`ܤ�\�,��o���9_Ga�>=E\������y� }�7     