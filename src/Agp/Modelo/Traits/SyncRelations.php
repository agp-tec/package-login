<?php


namespace Agp\Modelo\Traits;


use Agp\Modelo\Model\Entity\Pivot;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

trait SyncRelations
{
    protected $fillableRelations = [];
    public $toDeleteForSync = false;
    public $pivotToDeleteForSync = null;
    public $attFkKeyName = null;

    /**
     * @return array
     */
    public function getFillableRelations(): array
    {
        return isset($this->fillableRelations) ?
            $this->fillableRelations : [];
    }

    public function sync($data){
        $this->syncData($this, $data);
        $this->synchronized = true;
    }

    private function syncData($model, $data){
        foreach ($data as $key => $d){
            $fillableRelations = $model->getFillableRelations();
            if (in_array(Str::camel($key), $fillableRelations)){
                $this->syncRelation($model, Str::camel($key), $d);
            }else{
                if (in_array($key, $model->getFillable()) && ($model->{$key} == null || $model->{$key} != $d))
                    $model->{$key} = $d;
            }
        }
    }

    private function syncRelation(Model $model, $relationName, $data){
        $relation = $model->{$relationName}();
        $related = $relation->getRelated();

        $relationType = get_class($relation);
        switch ($relationType){
            case BelongsToMany::class:
                if (!array_key_exists($relationName, $model->getRelations())) {
                    $model->loadMissing($relationName);
                }
                $relationInstance = $model->getRelation($relationName);

                foreach ($relationInstance as $m){
                    $pivot = $m->getRelation($relation->getPivotAccessor());
                    if ($pivot != null) {
                        $m->toDeleteForSync = true;
                        $m->pivotToDeleteForSync = $pivot;
                    }
                }

                if (!is_array($data))
                    break;

                foreach ($data as $d){
                    if (is_numeric($d)) {
                        $relatedKey = $d;
                    }else if (is_array($d) && Arr::exists($d, $relation->getRelatedKeyName())) {
                        $relatedKey = $d[$relation->getRelatedKeyName()];
                    }else{
                        throw new RuntimeException("relatedKey inválida");
                    }

                    $m = $relationInstance->find($relatedKey);
                    if ($m == null){
                        $m = $related->findOrFail($relatedKey);
                        $pivot = $relation->newPivot([
                            $relation->getRelatedPivotKeyName() => $relatedKey
                        ]);
                        $pivot->attFkKeyName = $relation->getForeignPivotKeyName();
                        $m->setRelation($relation->getPivotAccessor(), $pivot);
                        $relationInstance->add($m);
                    }else{
                        $pivot = $m->getRelation($relation->getPivotAccessor());
                        $m->toDeleteForSync = false;
                        $m->pivotToDeleteForSync = null;
                    }

                    if (is_array($d) && array_key_exists('pivot', $d)) {
                        $this->syncData($pivot, $d['pivot']);
                    }
                }

                if ($model->usesTimestamps() && !$related->usesTimestamps())
                    $model->updateTimestamps();

                break;
            case BelongsTo::class:
                $relatedKey = null;
                if (is_numeric($data)) {
                    $relatedKey = $data;
                }else if (is_array($data) && Arr::exists($data, $related->getKeyName())) {
                    $relatedKey = $data[$related->getKeyName()];
                }else{
                    $relation->dissociate();
                }

                if ($relatedKey != null){
                    $obj = $related->findOrFail($relatedKey);
                    $relation->associate($obj);
                }

                break;
            case HasOne::class:
            case MorphMany::class:
            case HasMany::class:
                if ($relationType == HasOne::class) {
                    if ($this->getKey() != null && (!isset($data[$related->getKeyName()]) || $data[$related->getKeyName()] == null))
                        $data[$related->getKeyName()] = $this->getKey();

                    $data = [$data];
                }

                if (!array_key_exists($relationName, $model->getRelations())) {
                    $model->loadMissing($relationName);
                }

                if ($relationType == HasOne::class){
                    $relationInstance = new Collection();
                    $h = $model->getRelation($relationName);
                    if ($h != null)
                        $relationInstance->add($model->getRelation($relationName));
                }else{
                    $relationInstance = $model->getRelation($relationName);
                }

                foreach ($relationInstance as $m){
                    $m->toDeleteForSync = true;
                }

                if (!is_array($data))
                    break;

                foreach ($data as $key => $d){
                    $m = array_key_exists($related->getKeyName(), $d) && $d[$related->getKeyName()] > 0 ?
                        $relationInstance->find($d[$related->getKeyName()]) : null;

                    if ($m == null){
                        $m = $related->newInstance();
                        $m->attFkKeyName = $relation->getForeignKeyName();
                        $relationInstance->splice($key, 0, [$m]);
                    } else {
                        if ($relationType != HasOne::class || sizeof($d) > 1)
                            $m->toDeleteForSync = false;
                    }
                    $this->syncData($m, $d);
                }

                if ($relationType == HasOne::class && $model->getRelation($relationName) == null)
                    $model->setRelation($relationName, $m);

                if ($model->usesTimestamps() && !$related->usesTimestamps())
                    $model->updateTimestamps();

                break;
        }
    }

    /**
     * Save the model and all of its relationships.
     *
     * @return bool
     */
    public function push()
    {
        if (! $this->save()) {
            return false;
        }

        // To sync all of the relationships to the database, we will simply spin through
        // the relationships and save each model via this "push" method, which allows
        // us to recurse into all of these nested relations for the model instance.
        foreach ($this->relations as $relationName => $models) {
            $arr = $models instanceof Collection
                ? $models->all() : [$models];

            foreach (array_filter($arr) as $key => $model) {
                if ($model->toDeleteForSync === true) {
                    ($model->pivotToDeleteForSync != null) ?
                        $model->pivotToDeleteForSync->delete() :
                        $model->delete();

                    if ($models instanceof Collection) {
                        $models->forget($key);
                    }
                } else {
                    if ($model->attFkKeyName != null){
                        $key = $model instanceof Pivot ? $model->pivotParent->getKey() : $this->getKey();
                        $model->{$model->attFkKeyName} = $key;
                    }

                    if (!$model->push()) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
