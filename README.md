GfreeauCustomValidationPathBundle
=================================

This symfony2 bundle is still under development, it allows you to define custom directories for storing validation files.

This is useful if you store entities and models outside of bundles. Doctrine and JMSSerializer already allow this, but by default symfony only loads xml and yml validation configuration from within bundles.

Here is an example project structure where our non-framework code does not exist in bundles:

```
src
    Vendor
        Bundle
            AcmeBlogBundle
        Service
        Entity
        Resources
            config
                doctrine
                serializer
                validation
```

Here is an example config:

```
gfreeau_custom_validation_path:
    directories:
        -
            path: %kernel.root_dir%/../src/Vendor/Resources/config/validation
            type: xml
            recursive: true
        -
            path: %kernel.root_dir%/../src/Vendor/Resources/config/validation
            type: yml
            recursive: false
```

You will know if your validation files have been included by running:

```
./app/console container:debug --format=json --parameters
```

look for the key validator.mapping.loader.xml_files_loader.mapping_files or validator.mapping.loader.yml_files_loader.mapping_files

Here is the example config for orm and serializer to go along with custom validation directories:

```
doctrine:
    orm:
        auto_mapping: true
        mappings:
            mymodel:
                type: xml
                dir: %kernel.root_dir%/../src/Vendor/Resources/config/doctrine
                prefix: Vendor\Entity
                alias: VendorEntity
                is_bundle: false

jms_serializer:
    metadata:
        directories:
            myvendor:
                namespace_prefix: "Vendor\\"
                path: %kernel.root_dir%/../src/Vendor/Resources/config/serializer
```
