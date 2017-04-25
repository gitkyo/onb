<?php

/**
 * In order to create relationships between reference fields, CER needs to know
 * about what reference fields are available, and how to handle them, which is
 * what this hook is for. It should always return an array, even if there are
 * no fields to expose. The ultimate goal of this hook is to define a flattened
 * hierarchy of all the reference-type fields that CER can use.
 */
function hook_cer_fields() {
  return array(
    // The keys should refer to a single field instance on a single bundle of a single
    // entity type, even for embedded entities like field collections (see below).
    'node:article:field_related_pages' => array(
      // At the very least, each field you return here needs to have a 'class' set,
      // which is the class of the plugin which will handle this field. A CER field
      // plugin must be a sub-class of CerField, and there must be a separate plugin
      // for each *type* of field you want to integrate (CER provides support for
      // most reference-type fields out of the box, though). The class you provide
      // MUST be registered with the autoloader (i.e., you need to mention it in the
      // files[] array in your module's info file).
      'class' => 'CerEntityReferenceField',
    ),
    // A field collection field is a type of reference field, so you can expose these
    // to CER too. If you want to refer to reference fields on field collections, you
    // must define the parent fields too, as in this example.
    'node:page:field_my_field_collection' => array(
      'class' => 'CerFieldCollectionField',
    ),
    'field_collection_item:field_my_field_collection:field_related_articles' => array(
      'class' => 'CerEntityReferenceField',
      // For fields that are embedded in other entities (the prime example being field
      // collections), the possible parents of the field need to be defined. The array
      // of parents should be an array of keys that are present in the aggregated result
      // of hook_cer_fields(). There could be many possible parents for a single field;
      // each parent represents another possible "route" to this field. If you leave 
      // this out, CER will try to automatically detect the parents.
      'parents' => array(
        'node:page:field_my_field_collection',
      ),
      // Embedded fields might *require* a parent. At the time of this writing, this
      // really only applies to field collections. The "require parent" flag means that
      // this field MUST have at least one parent, or CER won't use it. You can probably
      // omit this key, and let CER detect the correct value.
      'require parent' => TRUE,
    ),
  );
}

/**
 * Alter the information gathered by hook_cer_fields().
 */
function hook_cer_fields_alter(array &$fields) {
  // @todo
}

/**
 * Provide default presets.
 */
function hook_cer_default_presets() {
  $presets = array();

  $preset = new CerPreset();

  // A CER preset consists of a left field chain, and a right field chain. A field chain
  // is really just a glorified array of field plugins.
  $left = new CerFieldChain();

  // You can use CerField::getPlugin() to fetch a field plugin for any field that has
  // been defined in hook_cer_fields(). You can also do it the hard way:
  //
  // $field = new MyCerFieldPluginClass($entity_type, $bundle, $field_name);
  //
  $left->addField(CerField::getPlugin('node:article:field_related_pages'));

  $preset->left = $left;

  $right = new CerFieldChain();
  // Field plugin instances are added to field chains from the inside out. Start with the
  // innermost field, working towards the top level.
  $right->addField(CerField::getPlugin('field_collection_item:field_my_field_collection:field_related_articles'));
  $right->addField(CerField::getPlugin('node:page:field_my_field_collection'));
  $preset->right = $right;

  // $preset->id is automatically generated by the CerPreset object. It's just an MD5
  // hash of the left and right field chains' string representations.
  $presets[$preset->id] = $preset;
  
  return $presets;
}

/**
 * Alter default presets.
 */
function hook_cer_default_presets_alter(array &$presets) {
}

/**
 * React to the creation of a preset. This hook is invoked before the preset
 * is saved to the database, so you can alter its properties by reference.
 */
function hook_cer_preset_create(CerPreset $preset) {
}

/**
 * React to a preset being enabled or disabled. This hook is invoked after the 
 * preset's status has been changed. The preset's new status (boolean) will be
 * in $preset->enabled.
 */
function hook_cer_preset_toggle(CerPreset $preset) {
}

/**
 * React to the deletion of a preset. This hook is only invoked when the preset
 * is permanently deleted, not when it's reverted to its default state (if it
 * has one).
 */
function hook_cer_preset_delete(CerPreset $preset) {
}

/**
 * Invoked before CER processes an entity insert. This gives modules a chance
 * to influence which presets are going to be executed.
 *
 * $presets is an array of the presets CER has loaded for the entity, keyed
 * by their computed IDs. You can add presets to the queue, or delete presets
 * from the queue. $entity is a metadata wrapper around the entity which
 * will be used for the *left* side of each preset. (In CER terminology, the
 * "left" entity is the entity that has references to the "right" entities.
 * Only the right entities are ever modified during processing -- the left
 * entity is really only used to get to the right entities.)
 */
function hook_cer_entity_insert(array &$presets, EntityDrupalWrapper $entity) {
}

/**
 * Like hook_cer_entity_insert(), but acts only on entity update, including CER's
 * bulk update.
 */
function hook_cer_entity_update(array &$presets, EntityDrupalWrapper $entity) {
}

/**
 * Like hook_cer_entity_insert() and hook_cer_entity_update(), but acts on an entity
 * after it's been deleted.
 */
function hook_cer_entity_delete(array &$presets, EntityDrupalWrapper $entity) {
}