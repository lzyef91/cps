<?php

/**
 * A helper file for Dcat Admin, to provide autocomplete information to your IDE
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author jqh <841324345@qq.com>
 */
namespace Dcat\Admin {
    use Illuminate\Support\Collection;

    /**
     * @property Grid\Column|Collection brands
     * @property Grid\Column|Collection id
     * @property Grid\Column|Collection name
     * @property Grid\Column|Collection type
     * @property Grid\Column|Collection version
     * @property Grid\Column|Collection detail
     * @property Grid\Column|Collection created_at
     * @property Grid\Column|Collection updated_at
     * @property Grid\Column|Collection is_enabled
     * @property Grid\Column|Collection parent_id
     * @property Grid\Column|Collection order
     * @property Grid\Column|Collection icon
     * @property Grid\Column|Collection uri
     * @property Grid\Column|Collection extension
     * @property Grid\Column|Collection permission_id
     * @property Grid\Column|Collection menu_id
     * @property Grid\Column|Collection slug
     * @property Grid\Column|Collection http_method
     * @property Grid\Column|Collection http_path
     * @property Grid\Column|Collection role_id
     * @property Grid\Column|Collection user_id
     * @property Grid\Column|Collection value
     * @property Grid\Column|Collection username
     * @property Grid\Column|Collection password
     * @property Grid\Column|Collection avatar
     * @property Grid\Column|Collection remember_token
     * @property Grid\Column|Collection code
     * @property Grid\Column|Collection region
     * @property Grid\Column|Collection province_code
     * @property Grid\Column|Collection province
     * @property Grid\Column|Collection city_code
     * @property Grid\Column|Collection city
     * @property Grid\Column|Collection district_code
     * @property Grid\Column|Collection district
     * @property Grid\Column|Collection fixed_code
     * @property Grid\Column|Collection uuid
     * @property Grid\Column|Collection batch_id
     * @property Grid\Column|Collection path
     * @property Grid\Column|Collection disk
     * @property Grid\Column|Collection exception
     * @property Grid\Column|Collection exception_msg
     * @property Grid\Column|Collection failed_at
     * @property Grid\Column|Collection succeed_at
     * @property Grid\Column|Collection finished_at
     * @property Grid\Column|Collection start_at
     * @property Grid\Column|Collection connection
     * @property Grid\Column|Collection queue
     * @property Grid\Column|Collection payload
     * @property Grid\Column|Collection total_jobs
     * @property Grid\Column|Collection pending_jobs
     * @property Grid\Column|Collection failed_jobs
     * @property Grid\Column|Collection failed_job_ids
     * @property Grid\Column|Collection cancelled_at
     * @property Grid\Column|Collection email
     * @property Grid\Column|Collection token
     * @property Grid\Column|Collection tokenable_type
     * @property Grid\Column|Collection tokenable_id
     * @property Grid\Column|Collection abilities
     * @property Grid\Column|Collection last_used_at
     * @property Grid\Column|Collection email_verified_at
     * @property Grid\Column|Collection shop_id
     * @property Grid\Column|Collection qike_contact_id
     * @property Grid\Column|Collection contact_type
     * @property Grid\Column|Collection contact_no
     * @property Grid\Column|Collection duty
     * @property Grid\Column|Collection location
     * @property Grid\Column|Collection source_type
     * @property Grid\Column|Collection source_url
     * @property Grid\Column|Collection qike_enterprise_id
     * @property Grid\Column|Collection enterprise_type
     * @property Grid\Column|Collection enterprise_status
     * @property Grid\Column|Collection enterprise_uniscid
     * @property Grid\Column|Collection legal_person_name
     * @property Grid\Column|Collection region_code
     * @property Grid\Column|Collection region_province_code
     * @property Grid\Column|Collection region_province
     * @property Grid\Column|Collection region_city_code
     * @property Grid\Column|Collection region_city
     * @property Grid\Column|Collection region_district_code
     * @property Grid\Column|Collection region_district
     * @property Grid\Column|Collection size
     * @property Grid\Column|Collection established_at
     * @property Grid\Column|Collection brand_name
     * @property Grid\Column|Collection brand_name_en
     * @property Grid\Column|Collection brand_cert_type
     * @property Grid\Column|Collection brand_auth_level
     * @property Grid\Column|Collection brand_category
     * @property Grid\Column|Collection valid_time
     * @property Grid\Column|Collection category_name
     * @property Grid\Column|Collection category_code
     * @property Grid\Column|Collection major
     * @property Grid\Column|Collection kdt_id
     * @property Grid\Column|Collection address
     * @property Grid\Column|Collection mp_qrcode
     * @property Grid\Column|Collection principal_name
     * @property Grid\Column|Collection principal_type
     * @property Grid\Column|Collection principal_address
     * @property Grid\Column|Collection has_contacts
     * @property Grid\Column|Collection total_contacts
     * @property Grid\Column|Collection total_shops
     * @property Grid\Column|Collection other_shops
     * @property Grid\Column|Collection open_at
     * @property Grid\Column|Collection deleted_at
     *
     * @method Grid\Column|Collection brands(string $label = null)
     * @method Grid\Column|Collection id(string $label = null)
     * @method Grid\Column|Collection name(string $label = null)
     * @method Grid\Column|Collection type(string $label = null)
     * @method Grid\Column|Collection version(string $label = null)
     * @method Grid\Column|Collection detail(string $label = null)
     * @method Grid\Column|Collection created_at(string $label = null)
     * @method Grid\Column|Collection updated_at(string $label = null)
     * @method Grid\Column|Collection is_enabled(string $label = null)
     * @method Grid\Column|Collection parent_id(string $label = null)
     * @method Grid\Column|Collection order(string $label = null)
     * @method Grid\Column|Collection icon(string $label = null)
     * @method Grid\Column|Collection uri(string $label = null)
     * @method Grid\Column|Collection extension(string $label = null)
     * @method Grid\Column|Collection permission_id(string $label = null)
     * @method Grid\Column|Collection menu_id(string $label = null)
     * @method Grid\Column|Collection slug(string $label = null)
     * @method Grid\Column|Collection http_method(string $label = null)
     * @method Grid\Column|Collection http_path(string $label = null)
     * @method Grid\Column|Collection role_id(string $label = null)
     * @method Grid\Column|Collection user_id(string $label = null)
     * @method Grid\Column|Collection value(string $label = null)
     * @method Grid\Column|Collection username(string $label = null)
     * @method Grid\Column|Collection password(string $label = null)
     * @method Grid\Column|Collection avatar(string $label = null)
     * @method Grid\Column|Collection remember_token(string $label = null)
     * @method Grid\Column|Collection code(string $label = null)
     * @method Grid\Column|Collection region(string $label = null)
     * @method Grid\Column|Collection province_code(string $label = null)
     * @method Grid\Column|Collection province(string $label = null)
     * @method Grid\Column|Collection city_code(string $label = null)
     * @method Grid\Column|Collection city(string $label = null)
     * @method Grid\Column|Collection district_code(string $label = null)
     * @method Grid\Column|Collection district(string $label = null)
     * @method Grid\Column|Collection fixed_code(string $label = null)
     * @method Grid\Column|Collection uuid(string $label = null)
     * @method Grid\Column|Collection batch_id(string $label = null)
     * @method Grid\Column|Collection path(string $label = null)
     * @method Grid\Column|Collection disk(string $label = null)
     * @method Grid\Column|Collection exception(string $label = null)
     * @method Grid\Column|Collection exception_msg(string $label = null)
     * @method Grid\Column|Collection failed_at(string $label = null)
     * @method Grid\Column|Collection succeed_at(string $label = null)
     * @method Grid\Column|Collection finished_at(string $label = null)
     * @method Grid\Column|Collection start_at(string $label = null)
     * @method Grid\Column|Collection connection(string $label = null)
     * @method Grid\Column|Collection queue(string $label = null)
     * @method Grid\Column|Collection payload(string $label = null)
     * @method Grid\Column|Collection total_jobs(string $label = null)
     * @method Grid\Column|Collection pending_jobs(string $label = null)
     * @method Grid\Column|Collection failed_jobs(string $label = null)
     * @method Grid\Column|Collection failed_job_ids(string $label = null)
     * @method Grid\Column|Collection cancelled_at(string $label = null)
     * @method Grid\Column|Collection email(string $label = null)
     * @method Grid\Column|Collection token(string $label = null)
     * @method Grid\Column|Collection tokenable_type(string $label = null)
     * @method Grid\Column|Collection tokenable_id(string $label = null)
     * @method Grid\Column|Collection abilities(string $label = null)
     * @method Grid\Column|Collection last_used_at(string $label = null)
     * @method Grid\Column|Collection email_verified_at(string $label = null)
     * @method Grid\Column|Collection shop_id(string $label = null)
     * @method Grid\Column|Collection qike_contact_id(string $label = null)
     * @method Grid\Column|Collection contact_type(string $label = null)
     * @method Grid\Column|Collection contact_no(string $label = null)
     * @method Grid\Column|Collection duty(string $label = null)
     * @method Grid\Column|Collection location(string $label = null)
     * @method Grid\Column|Collection source_type(string $label = null)
     * @method Grid\Column|Collection source_url(string $label = null)
     * @method Grid\Column|Collection qike_enterprise_id(string $label = null)
     * @method Grid\Column|Collection enterprise_type(string $label = null)
     * @method Grid\Column|Collection enterprise_status(string $label = null)
     * @method Grid\Column|Collection enterprise_uniscid(string $label = null)
     * @method Grid\Column|Collection legal_person_name(string $label = null)
     * @method Grid\Column|Collection region_code(string $label = null)
     * @method Grid\Column|Collection region_province_code(string $label = null)
     * @method Grid\Column|Collection region_province(string $label = null)
     * @method Grid\Column|Collection region_city_code(string $label = null)
     * @method Grid\Column|Collection region_city(string $label = null)
     * @method Grid\Column|Collection region_district_code(string $label = null)
     * @method Grid\Column|Collection region_district(string $label = null)
     * @method Grid\Column|Collection size(string $label = null)
     * @method Grid\Column|Collection established_at(string $label = null)
     * @method Grid\Column|Collection brand_name(string $label = null)
     * @method Grid\Column|Collection brand_name_en(string $label = null)
     * @method Grid\Column|Collection brand_cert_type(string $label = null)
     * @method Grid\Column|Collection brand_auth_level(string $label = null)
     * @method Grid\Column|Collection brand_category(string $label = null)
     * @method Grid\Column|Collection valid_time(string $label = null)
     * @method Grid\Column|Collection category_name(string $label = null)
     * @method Grid\Column|Collection category_code(string $label = null)
     * @method Grid\Column|Collection major(string $label = null)
     * @method Grid\Column|Collection kdt_id(string $label = null)
     * @method Grid\Column|Collection address(string $label = null)
     * @method Grid\Column|Collection mp_qrcode(string $label = null)
     * @method Grid\Column|Collection principal_name(string $label = null)
     * @method Grid\Column|Collection principal_type(string $label = null)
     * @method Grid\Column|Collection principal_address(string $label = null)
     * @method Grid\Column|Collection has_contacts(string $label = null)
     * @method Grid\Column|Collection total_contacts(string $label = null)
     * @method Grid\Column|Collection total_shops(string $label = null)
     * @method Grid\Column|Collection other_shops(string $label = null)
     * @method Grid\Column|Collection open_at(string $label = null)
     * @method Grid\Column|Collection deleted_at(string $label = null)
     */
    class Grid {}

    class MiniGrid extends Grid {}

    /**
     * @property Show\Field|Collection brands
     * @property Show\Field|Collection id
     * @property Show\Field|Collection name
     * @property Show\Field|Collection type
     * @property Show\Field|Collection version
     * @property Show\Field|Collection detail
     * @property Show\Field|Collection created_at
     * @property Show\Field|Collection updated_at
     * @property Show\Field|Collection is_enabled
     * @property Show\Field|Collection parent_id
     * @property Show\Field|Collection order
     * @property Show\Field|Collection icon
     * @property Show\Field|Collection uri
     * @property Show\Field|Collection extension
     * @property Show\Field|Collection permission_id
     * @property Show\Field|Collection menu_id
     * @property Show\Field|Collection slug
     * @property Show\Field|Collection http_method
     * @property Show\Field|Collection http_path
     * @property Show\Field|Collection role_id
     * @property Show\Field|Collection user_id
     * @property Show\Field|Collection value
     * @property Show\Field|Collection username
     * @property Show\Field|Collection password
     * @property Show\Field|Collection avatar
     * @property Show\Field|Collection remember_token
     * @property Show\Field|Collection code
     * @property Show\Field|Collection region
     * @property Show\Field|Collection province_code
     * @property Show\Field|Collection province
     * @property Show\Field|Collection city_code
     * @property Show\Field|Collection city
     * @property Show\Field|Collection district_code
     * @property Show\Field|Collection district
     * @property Show\Field|Collection fixed_code
     * @property Show\Field|Collection uuid
     * @property Show\Field|Collection batch_id
     * @property Show\Field|Collection path
     * @property Show\Field|Collection disk
     * @property Show\Field|Collection exception
     * @property Show\Field|Collection exception_msg
     * @property Show\Field|Collection failed_at
     * @property Show\Field|Collection succeed_at
     * @property Show\Field|Collection finished_at
     * @property Show\Field|Collection start_at
     * @property Show\Field|Collection connection
     * @property Show\Field|Collection queue
     * @property Show\Field|Collection payload
     * @property Show\Field|Collection total_jobs
     * @property Show\Field|Collection pending_jobs
     * @property Show\Field|Collection failed_jobs
     * @property Show\Field|Collection failed_job_ids
     * @property Show\Field|Collection cancelled_at
     * @property Show\Field|Collection email
     * @property Show\Field|Collection token
     * @property Show\Field|Collection tokenable_type
     * @property Show\Field|Collection tokenable_id
     * @property Show\Field|Collection abilities
     * @property Show\Field|Collection last_used_at
     * @property Show\Field|Collection email_verified_at
     * @property Show\Field|Collection shop_id
     * @property Show\Field|Collection qike_contact_id
     * @property Show\Field|Collection contact_type
     * @property Show\Field|Collection contact_no
     * @property Show\Field|Collection duty
     * @property Show\Field|Collection location
     * @property Show\Field|Collection source_type
     * @property Show\Field|Collection source_url
     * @property Show\Field|Collection qike_enterprise_id
     * @property Show\Field|Collection enterprise_type
     * @property Show\Field|Collection enterprise_status
     * @property Show\Field|Collection enterprise_uniscid
     * @property Show\Field|Collection legal_person_name
     * @property Show\Field|Collection region_code
     * @property Show\Field|Collection region_province_code
     * @property Show\Field|Collection region_province
     * @property Show\Field|Collection region_city_code
     * @property Show\Field|Collection region_city
     * @property Show\Field|Collection region_district_code
     * @property Show\Field|Collection region_district
     * @property Show\Field|Collection size
     * @property Show\Field|Collection established_at
     * @property Show\Field|Collection brand_name
     * @property Show\Field|Collection brand_name_en
     * @property Show\Field|Collection brand_cert_type
     * @property Show\Field|Collection brand_auth_level
     * @property Show\Field|Collection brand_category
     * @property Show\Field|Collection valid_time
     * @property Show\Field|Collection category_name
     * @property Show\Field|Collection category_code
     * @property Show\Field|Collection major
     * @property Show\Field|Collection kdt_id
     * @property Show\Field|Collection address
     * @property Show\Field|Collection mp_qrcode
     * @property Show\Field|Collection principal_name
     * @property Show\Field|Collection principal_type
     * @property Show\Field|Collection principal_address
     * @property Show\Field|Collection has_contacts
     * @property Show\Field|Collection total_contacts
     * @property Show\Field|Collection total_shops
     * @property Show\Field|Collection other_shops
     * @property Show\Field|Collection open_at
     * @property Show\Field|Collection deleted_at
     *
     * @method Show\Field|Collection brands(string $label = null)
     * @method Show\Field|Collection id(string $label = null)
     * @method Show\Field|Collection name(string $label = null)
     * @method Show\Field|Collection type(string $label = null)
     * @method Show\Field|Collection version(string $label = null)
     * @method Show\Field|Collection detail(string $label = null)
     * @method Show\Field|Collection created_at(string $label = null)
     * @method Show\Field|Collection updated_at(string $label = null)
     * @method Show\Field|Collection is_enabled(string $label = null)
     * @method Show\Field|Collection parent_id(string $label = null)
     * @method Show\Field|Collection order(string $label = null)
     * @method Show\Field|Collection icon(string $label = null)
     * @method Show\Field|Collection uri(string $label = null)
     * @method Show\Field|Collection extension(string $label = null)
     * @method Show\Field|Collection permission_id(string $label = null)
     * @method Show\Field|Collection menu_id(string $label = null)
     * @method Show\Field|Collection slug(string $label = null)
     * @method Show\Field|Collection http_method(string $label = null)
     * @method Show\Field|Collection http_path(string $label = null)
     * @method Show\Field|Collection role_id(string $label = null)
     * @method Show\Field|Collection user_id(string $label = null)
     * @method Show\Field|Collection value(string $label = null)
     * @method Show\Field|Collection username(string $label = null)
     * @method Show\Field|Collection password(string $label = null)
     * @method Show\Field|Collection avatar(string $label = null)
     * @method Show\Field|Collection remember_token(string $label = null)
     * @method Show\Field|Collection code(string $label = null)
     * @method Show\Field|Collection region(string $label = null)
     * @method Show\Field|Collection province_code(string $label = null)
     * @method Show\Field|Collection province(string $label = null)
     * @method Show\Field|Collection city_code(string $label = null)
     * @method Show\Field|Collection city(string $label = null)
     * @method Show\Field|Collection district_code(string $label = null)
     * @method Show\Field|Collection district(string $label = null)
     * @method Show\Field|Collection fixed_code(string $label = null)
     * @method Show\Field|Collection uuid(string $label = null)
     * @method Show\Field|Collection batch_id(string $label = null)
     * @method Show\Field|Collection path(string $label = null)
     * @method Show\Field|Collection disk(string $label = null)
     * @method Show\Field|Collection exception(string $label = null)
     * @method Show\Field|Collection exception_msg(string $label = null)
     * @method Show\Field|Collection failed_at(string $label = null)
     * @method Show\Field|Collection succeed_at(string $label = null)
     * @method Show\Field|Collection finished_at(string $label = null)
     * @method Show\Field|Collection start_at(string $label = null)
     * @method Show\Field|Collection connection(string $label = null)
     * @method Show\Field|Collection queue(string $label = null)
     * @method Show\Field|Collection payload(string $label = null)
     * @method Show\Field|Collection total_jobs(string $label = null)
     * @method Show\Field|Collection pending_jobs(string $label = null)
     * @method Show\Field|Collection failed_jobs(string $label = null)
     * @method Show\Field|Collection failed_job_ids(string $label = null)
     * @method Show\Field|Collection cancelled_at(string $label = null)
     * @method Show\Field|Collection email(string $label = null)
     * @method Show\Field|Collection token(string $label = null)
     * @method Show\Field|Collection tokenable_type(string $label = null)
     * @method Show\Field|Collection tokenable_id(string $label = null)
     * @method Show\Field|Collection abilities(string $label = null)
     * @method Show\Field|Collection last_used_at(string $label = null)
     * @method Show\Field|Collection email_verified_at(string $label = null)
     * @method Show\Field|Collection shop_id(string $label = null)
     * @method Show\Field|Collection qike_contact_id(string $label = null)
     * @method Show\Field|Collection contact_type(string $label = null)
     * @method Show\Field|Collection contact_no(string $label = null)
     * @method Show\Field|Collection duty(string $label = null)
     * @method Show\Field|Collection location(string $label = null)
     * @method Show\Field|Collection source_type(string $label = null)
     * @method Show\Field|Collection source_url(string $label = null)
     * @method Show\Field|Collection qike_enterprise_id(string $label = null)
     * @method Show\Field|Collection enterprise_type(string $label = null)
     * @method Show\Field|Collection enterprise_status(string $label = null)
     * @method Show\Field|Collection enterprise_uniscid(string $label = null)
     * @method Show\Field|Collection legal_person_name(string $label = null)
     * @method Show\Field|Collection region_code(string $label = null)
     * @method Show\Field|Collection region_province_code(string $label = null)
     * @method Show\Field|Collection region_province(string $label = null)
     * @method Show\Field|Collection region_city_code(string $label = null)
     * @method Show\Field|Collection region_city(string $label = null)
     * @method Show\Field|Collection region_district_code(string $label = null)
     * @method Show\Field|Collection region_district(string $label = null)
     * @method Show\Field|Collection size(string $label = null)
     * @method Show\Field|Collection established_at(string $label = null)
     * @method Show\Field|Collection brand_name(string $label = null)
     * @method Show\Field|Collection brand_name_en(string $label = null)
     * @method Show\Field|Collection brand_cert_type(string $label = null)
     * @method Show\Field|Collection brand_auth_level(string $label = null)
     * @method Show\Field|Collection brand_category(string $label = null)
     * @method Show\Field|Collection valid_time(string $label = null)
     * @method Show\Field|Collection category_name(string $label = null)
     * @method Show\Field|Collection category_code(string $label = null)
     * @method Show\Field|Collection major(string $label = null)
     * @method Show\Field|Collection kdt_id(string $label = null)
     * @method Show\Field|Collection address(string $label = null)
     * @method Show\Field|Collection mp_qrcode(string $label = null)
     * @method Show\Field|Collection principal_name(string $label = null)
     * @method Show\Field|Collection principal_type(string $label = null)
     * @method Show\Field|Collection principal_address(string $label = null)
     * @method Show\Field|Collection has_contacts(string $label = null)
     * @method Show\Field|Collection total_contacts(string $label = null)
     * @method Show\Field|Collection total_shops(string $label = null)
     * @method Show\Field|Collection other_shops(string $label = null)
     * @method Show\Field|Collection open_at(string $label = null)
     * @method Show\Field|Collection deleted_at(string $label = null)
     */
    class Show {}

    /**
     
     */
    class Form {}

}

namespace Dcat\Admin\Grid {
    /**
     
     */
    class Column {}

    /**
     
     */
    class Filter {}
}

namespace Dcat\Admin\Show {
    /**
     
     */
    class Field {}
}
