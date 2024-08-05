#define NAPI_VERSION 3

#include <napi-macros.h>
#include <node_api.h>
#include <assert.h>

#include <leveldb/db.h>
#include <leveldb/write_batch.h>
#include <leveldb/cache.h>
#include <leveldb/filter_policy.h>

#include <map>
#include <vector>

/**
 * Forward declarations.
 */
struct Database;
struct Iterator;
static void iterator_end_do (napi_env env, Iterator* iterator, napi_value cb);

/**
 * Macros.
 */

#define NAPI_DB_CONTEXT() \
  Database* database = NULL; \
  NAPI_STATUS_THROWS(napi_get_value_external(env, argv[0], (void**)&database));

#define NAPI_ITERATOR_CONTEXT() \
  Iterator* iterator = NULL; \
  NAPI_STATUS_THROWS(napi_get_value_external(env, argv[0], (void**)&iterator));

#define NAPI_BATCH_CONTEXT() \
  Batch* batch = NULL; \
  NAPI_STATUS_THROWS(napi_get_value_external(env, argv[0], (void**)&batch));

#define NAPI_RETURN_UNDEFINED() \
  return 0;

#define NAPI_UTF8_NEW(name, val)                \
  size_t name##_size = 0;                                               \
  NAPI_STATUS_THROWS(napi_get_value_string_utf8(env, val, NULL, 0, &name##_size)) \
  char* name = new char[name##_size + 1];                               \
  NAPI_STATUS_THROWS(napi_get_value_string_utf8(env, val, name, name##_size + 1, &name##_size)) \
  name[name##_size] = '\0';

#define NAPI_ARGV_UTF8_NEW(name, i) \
  NAPI_UTF8_NEW(name, argv[i])

#define LD_STRING_OR_BUFFER_TO_COPY(env, from, to)                      \
  char* to##Ch_ = 0;                                                    \
  size_t to##Sz_ = 0;                                                   \
  if (IsString(env, from)) {                                            \
    napi_get_value_string_utf8(env, from, NULL, 0, &to##Sz_);           \
    to##Ch_ = new char[to##Sz_ + 1];                                    \
    napi_get_value_string_utf8(env, from, to##Ch_, to##Sz_ + 1, &to##Sz_); \
    to##Ch_[to##Sz_] = '\0';                                            \
  } else if (IsBuffer(env, from)) {                                     \
    char* buf = 0;                                                      \
    napi_get_buffer_info(env, from, (void **)&buf, &to##Sz_);           \
    to##Ch_ = new char[to##Sz_];                                        \
    memcpy(to##Ch_, buf, to##Sz_);                                      \
  }

/*********************************************************************
 * Helpers.
 ********************************************************************/

/**
 * Returns true if 'value' is a string.
 */
static bool IsString (napi_env env, napi_value value) {
  napi_valuetype type;
  napi_typeof(env, value, &type);
  return type == napi_string;
}

/**
 * Returns true if 'value' is a buffer.
 */
static bool IsBuffer (napi_env env, napi_value value) {
  bool isBuffer;
  napi_is_buffer(env, value, &isBuffer);
  return isBuffer;
}

/**
 * Returns true if 'value' is an object.
 */
static bool IsObject (napi_env env, napi_value value) {
  napi_valuetype type;
  napi_typeof(env, value, &type);
  return type == napi_object;
}

/**
 * Create an error object.
 */
static napi_value CreateError (napi_env env, const char* str) {
  napi_value msg;
  napi_create_string_utf8(env, str, strlen(str), &msg);
  napi_value error;
  napi_create_error(env, NULL, msg, &error);
  return error;
}

/**
 * Returns true if 'obj' has a property 'key'.
 */
static bool HasProperty (napi_env env, napi_value obj, const char* key) {
  bool has = false;
  napi_has_named_property(env, obj, key, &has);
  return has;
}

/**
 * Returns a property in napi_value form.
 */
static napi_value GetProperty (napi_env env, napi_value obj, const char* key) {
  napi_value value;
  napi_get_named_property(env, obj, key, &value);
  return value;
}

/**
 * Returns a boolean property 'key' from 'obj'.
 * Returns 'DEFAULT' if the property doesn't exist.
 */
static bool BooleanProperty (napi_env env, napi_value obj, const char* key,
                             bool DEFAULT) {
  if (HasProperty(env, obj, key)) {
    napi_value value = GetProperty(env, obj, key);
    bool result;
    napi_get_value_bool(env, value, &result);
    return result;
  }

  return DEFAULT;
}

/**
 * Returns a uint32 property 'key' from 'obj'.
 * Returns 'DEFAULT' if the property doesn't exist.
 */
static uint32_t Uint32Property (napi_env env, napi_value obj, const char* key,
                                uint32_t DEFAULT) {
  if (HasProperty(env, obj, key)) {
    napi_value value = GetProperty(env, obj, key);
    uint32_t result;
    napi_get_value_uint32(env, value, &result);
    return result;
  }

  return DEFAULT;
}

/**
 * Returns a int32 property 'key' from 'obj'.
 * Returns 'DEFAULT' if the property doesn't exist.
 */
static int Int32Property (napi_env env, napi_value obj, const char* key,
                          int DEFAULT) {
  if (HasProperty(env, obj, key)) {
    napi_value value = GetProperty(env, obj, key);
    int result;
    napi_get_value_int32(env, value, &result);
    return result;
  }

  return DEFAULT;
}

/**
 * Returns a string property 'key' from 'obj'.
 * Returns empty string if the property doesn't exist.
 */
static std::string StringProperty (napi_env env, napi_value obj, const char* key) {
  if (HasProperty(env, obj, key)) {
    napi_value value = GetProperty(env, obj, key);
    if (IsString(env, value)) {
      size_t size = 0;
      napi_get_value_string_utf8(env, value, NULL, 0, &size);

      char* buf = new char[size + 1];
      napi_get_value_string_utf8(env, value, buf, size + 1, &size);
      buf[size] = '\0';

      std::string result = buf;
      delete [] buf;
      return result;
    }
  }

  return "";
}

static void DisposeSliceBuffer (leveldb::Slice slice) {
  if (!slice.empty()) delete [] slice.data();
}

/**
 * Convert a napi_value to a leveldb::Slice.
 */
static leveldb::Slice ToSlice (napi_env env, napi_value from) {
  LD_STRING_OR_BUFFER_TO_COPY(env, from, to);
  return leveldb::Slice(toCh_, toSz_);
}

/**
 * Returns length of string or buffer
 */
static size_t StringOrBufferLength (napi_env env, napi_value value) {
  size_t size = 0;

  if (IsString(env, value)) {
    napi_get_value_string_utf8(env, value, NULL, 0, &size);
  } else if (IsBuffer(env, value)) {
    char* buf;
    napi_get_buffer_info(env, value, (void **)&buf, &size);
  }

  return size;
}

/**
 * Takes a Buffer or string property 'name' from 'opts'.
 * Returns null if the property does not exist or is zero-length.
 */
static std::string* RangeOption (napi_env env, napi_value opts, const char* name) {
  if (HasProperty(env, opts, name)) {
    napi_value value = GetProperty(env, opts, name);

    if (StringOrBufferLength(env, value) > 0) {
      LD_STRING_OR_BUFFER_TO_COPY(env, value, to);
      std::string* result = new std::string(toCh_, toSz_);
      delete [] toCh_;
      return result;
    }
  }

  return NULL;
}

/**
 * Converts an array containing Buffer or string keys to a vector.
 * Empty elements are skipped.
 */
static std::vector<std::string>* KeyArray (napi_env env, napi_value arr) {
  uint32_t length;
  std::vector<std::string>* result = new std::vector<std::string>();

  if (napi_get_array_length(env, arr, &length) == napi_ok) {
    result->reserve(length);

    for (uint32_t i = 0; i < length; i++) {
      napi_value element;

      if (napi_get_element(env, arr, i, &element) == napi_ok &&
          StringOrBufferLength(env, element) > 0) {
        LD_STRING_OR_BUFFER_TO_COPY(env, element, to);
        result->emplace_back(toCh_, toSz_);
        delete [] toCh_;
      }
    }
  }

  return result;
}

/**
 * Calls a function.
 */
static napi_status CallFunction (napi_env env,
                                 napi_value callback,
                                 const int argc,
                                 napi_value* argv) {
  napi_value global;
  napi_get_global(env, &global);
  return napi_call_function(env, global, callback, argc, argv, NULL);
}

/**
 * Whether to yield entries, keys or values.
 */
enum Mode {
  entries,
  keys,
  values
};

/**
 * Helper struct for caching and converting a key-value pair to napi_values.
 */
struct Entry {
  Entry (const leveldb::Slice* key, const leveldb::Slice* value) {
    key_ = key != NULL ? new std::string(key->data(), key->size()) : NULL;
    value_ = value != NULL ? new std::string(value->data(), value->size()) : NULL;
  }

  ~Entry () {
    if (key_ != NULL) delete key_;
    if (value_ != NULL) delete value_;
  }

  // Not used yet.
  void ConvertXX (napi_env env, Mode mode, bool keyAsBuffer, bool valueAsBuffer, napi_value* result) {
    if (mode == Mode::entries) {
      napi_create_array_with_length(env, 2, result);

      napi_value valueElement;
      napi_value keyElement;

      Convert(env, key_, keyAsBuffer, &keyElement);
      Convert(env, value_, valueAsBuffer, &valueElement);

      napi_set_element(env, *result, 0, keyElement);
      napi_set_element(env, *result, 1, valueElement);
    } else if (mode == Mode::keys) {
      Convert(env, key_, keyAsBuffer, result);
    } else {
      Convert(env, value_, valueAsBuffer, result);
    }
  }

  static void Convert (napi_env env, const std::string* s, bool asBuffer, napi_value* result) {
    if (s == NULL) {
      napi_get_undefined(env, result);
    } else if (asBuffer) {
      napi_create_buffer_copy(env, s->size(), s->data(), NULL, result);
    } else {
      napi_create_string_utf8(env, s->data(), s->size(), result);
    }
  }

private:
  std::string* key_;
  std::string* value_;
};

/**
 * Base worker class. Handles the async work. Derived classes can override the
 * following virtual methods (listed in the order in which they're called):
 *
 * - DoExecute (abstract, worker pool thread): main work
 * - HandleOKCallback (main thread): call JS callback on success
 * - HandleErrorCallback (main thread): call JS callback on error
 * - DoFinally (main thread): do cleanup regardless of success
 */
struct BaseWorker {
  // Note: storing env is discouraged as we'd end up using it in unsafe places.
  BaseWorker (napi_env env,
              Database* database,
              napi_value callback,
              const char* resourceName)
    : database_(database), errMsg_(NULL) {
    NAPI_STATUS_THROWS_VOID(napi_create_reference(env, callback, 1, &callbackRef_));
    napi_value asyncResourceName;
    NAPI_STATUS_THROWS_VOID(napi_create_string_utf8(env, resourceName,
                                               NAPI_AUTO_LENGTH,
                                               &asyncResourceName));
    NAPI_STATUS_THROWS_VOID(napi_create_async_work(env, callback,
                                              asyncResourceName,
                                              BaseWorker::Execute,
                                              BaseWorker::Complete,
                                              this, &asyncWork_));
  }

  virtual ~BaseWorker () {
    delete [] errMsg_;
  }

  static void Execute (napi_env env, void* data) {
    BaseWorker* self = (BaseWorker*)data;

    // Don't pass env to DoExecute() because use of Node-API
    // methods should generally be avoided in async work.
    self->DoExecute();
  }

  bool SetStatus (leveldb::Status status) {
    status_ = status;
    if (!status.ok()) {
      SetErrorMessage(status.ToString().c_str());
      return false;
    }
    return true;
  }

  void SetErrorMessage(const char *msg) {
    delete [] errMsg_;
    size_t size = strlen(msg) + 1;
    errMsg_ = new char[size];
    memcpy(errMsg_, msg, size);
  }

  virtual void DoExecute () = 0;

  static void Complete (napi_env env, napi_status status, void* data) {
    BaseWorker* self = (BaseWorker*)data;

    self->DoComplete(env);
    self->DoFinally(env);
  }

  void DoComplete (napi_env env) {
    napi_value callback;
    napi_get_reference_value(env, callbackRef_, &callback);

    if (status_.ok()) {
      HandleOKCallback(env, callback);
    } else {
      HandleErrorCallback(env, callback);
    }
  }

  virtual void HandleOKCallback (napi_env env, napi_value callback) {
    napi_value argv;
    napi_get_null(env, &argv);
    CallFunction(env, callback, 1, &argv);
  }

  virtual void HandleErrorCallback (napi_env env, napi_value callback) {
    napi_value argv = CreateError(env, errMsg_);
    CallFunction(env, callback, 1, &argv);
  }

  virtual void DoFinally (napi_env env) {
    napi_delete_reference(env, callbackRef_);
    napi_delete_async_work(env, asyncWork_);

    delete this;
  }

  void Queue (napi_env env) {
    napi_queue_async_work(env, asyncWork_);
  }

  Database* database_;

private:
  napi_ref callbackRef_;
  napi_async_work asyncWork_;
  leveldb::Status status_;
  char *errMsg_;
};

/**
 * Owns the LevelDB storage, cache, filter policy and iterators.
 */
struct Database {
  Database ()
    : db_(NULL),
      blockCache_(NULL),
      filterPolicy_(leveldb::NewBloomFilterPolicy(10)),
      currentIteratorId_(0),
      pendingCloseWorker_(NULL),
      ref_(NULL),
      priorityWork_(0) {}

  ~Database () {
    if (db_ != NULL) {
      delete db_;
      db_ = NULL;
    }
  }

  leveldb::Status Open (const leveldb::Options& options,
                        const char* location) {
    return leveldb::DB::Open(options, location, &db_);
  }

  void CloseDatabase () {
    delete db_;
    db_ = NULL;
    if (blockCache_) {
      delete blockCache_;
      blockCache_ = NULL;
    }
  }

  leveldb::Status Put (const leveldb::WriteOptions& options,
                       leveldb::Slice key,
                       leveldb::Slice value) {
    return db_->Put(options, key, value);
  }

  leveldb::Status Get (const leveldb::ReadOptions& options,
                       leveldb::Slice key,
                       std::string& value) {
    return db_->Get(options, key, &value);
  }

  leveldb::Status Del (const leveldb::WriteOptions& options,
                       leveldb::Slice key) {
    return db_->Delete(options, key);
  }

  leveldb::Status WriteBatch (const leveldb::WriteOptions& options,
                              leveldb::WriteBatch* batch) {
    return db_->Write(options, batch);
  }

  uint64_t ApproximateSize (const leveldb::Range* range) {
    uint64_t size = 0;
    db_->GetApproximateSizes(range, 1, &size);
    return size;
  }

  void CompactRange (const leveldb::Slice* start,
                     const leveldb::Slice* end) {
    db_->CompactRange(start, end);
  }

  void GetProperty (const leveldb::Slice& property, std::string* value) {
    db_->GetProperty(property, value);
  }

  const leveldb::Snapshot* NewSnapshot () {
    return db_->GetSnapshot();
  }

  leveldb::Iterator* NewIterator (leveldb::ReadOptions* options) {
    return db_->NewIterator(*options);
  }

  void ReleaseSnapshot (const leveldb::Snapshot* snapshot) {
    return db_->ReleaseSnapshot(snapshot);
  }

  void AttachIterator (napi_env env, uint32_t id, Iterator* iterator) {
    iterators_[id] = iterator;
    IncrementPriorityWork(env);
  }

  void DetachIterator (napi_env env, uint32_t id) {
    iterators_.erase(id);
    DecrementPriorityWork(env);
  }

  void IncrementPriorityWork (napi_env env) {
    napi_reference_ref(env, ref_, &priorityWork_);
  }

  void DecrementPriorityWork (napi_env env) {
    napi_reference_unref(env, ref_, &priorityWork_);

    if (priorityWork_ == 0 && pendingCloseWorker_ != NULL) {
      pendingCloseWorker_->Queue(env);
      pendingCloseWorker_ = NULL;
    }
  }

  bool HasPriorityWork () const {
    return priorityWork_ > 0;
  }

  leveldb::DB* db_;
  leveldb::Cache* blockCache_;
  const leveldb::FilterPolicy* filterPolicy_;
  uint32_t currentIteratorId_;
  BaseWorker *pendingCloseWorker_;
  std::map< uint32_t, Iterator * > iterators_;
  napi_ref ref_;

private:
  uint32_t priorityWork_;
};

/**
 * Base worker class for doing async work that defers closing the database.
 */
struct PriorityWorker : public BaseWorker {
  PriorityWorker (napi_env env, Database* database, napi_value callback, const char* resourceName)
    : BaseWorker(env, database, callback, resourceName) {
      database_->IncrementPriorityWork(env);
  }

  virtual ~PriorityWorker () {}

  void DoFinally (napi_env env) override {
    database_->DecrementPriorityWork(env);
    BaseWorker::DoFinally(env);
  }
};

/**
 * Owns a leveldb iterator.
 */
struct BaseIterator {
  BaseIterator(Database* database,
               const bool reverse,
               std::string* lt,
               std::string* lte,
               std::string* gt,
               std::string* gte,
               const int limit,
               const bool fillCache)
    : database_(database),
      hasEnded_(false),
      didSeek_(false),
      reverse_(reverse),
      lt_(lt),
      lte_(lte),
      gt_(gt),
      gte_(gte),
      limit_(limit),
      count_(0) {
    options_ = new leveldb::ReadOptions();
    options_->fill_cache = fillCache;
    options_->snapshot = database->NewSnapshot();
    dbIterator_ = database_->NewIterator(options_);
  }

  virtual ~BaseIterator () {
    assert(hasEnded_);

    if (lt_ != NULL) delete lt_;
    if (gt_ != NULL) delete gt_;
    if (lte_ != NULL) delete lte_;
    if (gte_ != NULL) delete gte_;

    delete options_;
  }

  bool DidSeek () const {
    return didSeek_;
  }

  /**
   * Seek to the first relevant key based on range options.
   */
  void SeekToRange () {
    didSeek_ = true;

    if (!reverse_ && gte_ != NULL) {
      dbIterator_->Seek(*gte_);
    } else if (!reverse_ && gt_ != NULL) {
      dbIterator_->Seek(*gt_);

      if (dbIterator_->Valid() && dbIterator_->key().compare(*gt_) == 0) {
        dbIterator_->Next();
      }
    } else if (reverse_ && lte_ != NULL) {
      dbIterator_->Seek(*lte_);

      if (!dbIterator_->Valid()) {
        dbIterator_->SeekToLast();
      } else if (dbIterator_->key().compare(*lte_) > 0) {
        dbIterator_->Prev();
      }
    } else if (reverse_ && lt_ != NULL) {
      dbIterator_->Seek(*lt_);

      if (!dbIterator_->Valid()) {
        dbIterator_->SeekToLast();
      } else if (dbIterator_->key().compare(*lt_) >= 0) {
        dbIterator_->Prev();
      }
    } else if (reverse_) {
      dbIterator_->SeekToLast();
    } else {
      dbIterator_->SeekToFirst();
    }
  }

  /**
   * Seek manually (during iteration).
   */
  void Seek (leveldb::Slice& target) {
    didSeek_ = true;

    if (OutOfRange(target)) {
      return SeekToEnd();
    }

    dbIterator_->Seek(target);

    if (dbIterator_->Valid()) {
      int cmp = dbIterator_->key().compare(target);
      if (reverse_ ? cmp > 0 : cmp < 0) {
        Next();
      }
    } else {
      SeekToFirst();
      if (dbIterator_->Valid()) {
        int cmp = dbIterator_->key().compare(target);
        if (reverse_ ? cmp > 0 : cmp < 0) {
          SeekToEnd();
        }
      }
    }
  }

  void End () {
    if (!hasEnded_) {
      hasEnded_ = true;
      delete dbIterator_;
      dbIterator_ = NULL;
      database_->ReleaseSnapshot(options_->snapshot);
    }
  }

  bool Valid () const {
    return dbIterator_->Valid() && !OutOfRange(dbIterator_->key());
  }

  bool Increment () {
    return limit_ < 0 || ++count_ <= limit_;
  }

  void Next () {
    if (reverse_) dbIterator_->Prev();
    else dbIterator_->Next();
  }

  void SeekToFirst () {
    if (reverse_) dbIterator_->SeekToLast();
    else dbIterator_->SeekToFirst();
  }

  void SeekToLast () {
    if (reverse_) dbIterator_->SeekToFirst();
    else dbIterator_->SeekToLast();
  }

  void SeekToEnd () {
    SeekToLast();
    Next();
  }

  leveldb::Slice CurrentKey () const {
    return dbIterator_->key();
  }

  leveldb::Slice CurrentValue () const {
    return dbIterator_->value();
  }

  leveldb::Status Status () const {
    return dbIterator_->status();
  }

  bool OutOfRange (const leveldb::Slice& target) const {
    // TODO: benchmark to see if this is worth it
    // if (upperBoundOnly && !reverse_) {
    //   return ((lt_  != NULL && target.compare(*lt_) >= 0) ||
    //           (lte_ != NULL && target.compare(*lte_) > 0));
    // }

    return ((lt_  != NULL && target.compare(*lt_) >= 0) ||
            (lte_ != NULL && target.compare(*lte_) > 0) ||
            (gt_  != NULL && target.compare(*gt_) <= 0) ||
            (gte_ != NULL && target.compare(*gte_) < 0));
  }

  Database* database_;
  bool hasEnded_;

private:
  leveldb::Iterator* dbIterator_;
  bool didSeek_;
  const bool reverse_;
  std::string* lt_;
  std::string* lte_;
  std::string* gt_;
  std::string* gte_;
  const int limit_;
  int count_;
  leveldb::ReadOptions* options_;
};

/**
 * Extends BaseIterator for reading it from JS land.
 */
struct Iterator final : public BaseIterator {
  Iterator (Database* database,
            const uint32_t id,
            const bool reverse,
            const bool keys,
            const bool values,
            const int limit,
            std::string* lt,
            std::string* lte,
            std::string* gt,
            std::string* gte,
            const bool fillCache,
            const bool keyAsBuffer,
            const bool valueAsBuffer,
            const uint32_t highWaterMark)
    : BaseIterator(database, reverse, lt, lte, gt, gte, limit, fillCache),
      id_(id),
      keys_(keys),
      values_(values),
      keyAsBuffer_(keyAsBuffer),
      valueAsBuffer_(valueAsBuffer),
      highWaterMark_(highWaterMark),
      landed_(false),
      nexting_(false),
      isEnding_(false),
      endWorker_(NULL),
      ref_(NULL) {
  }

  ~Iterator () {}

  void Attach (napi_env env, napi_value context) {
    napi_create_reference(env, context, 1, &ref_);
    database_->AttachIterator(env, id_, this);
  }

  void Detach (napi_env env) {
    database_->DetachIterator(env, id_);
    if (ref_ != NULL) napi_delete_reference(env, ref_);
  }

  bool ReadMany (uint32_t size) {
    cache_.clear();
    size_t bytesRead = 0;

    while (true) {
      if (landed_) Next();
      if (!Valid() || !Increment()) break;

      if (keys_) {
        leveldb::Slice slice = CurrentKey();
        cache_.emplace_back(slice.data(), slice.size());
        bytesRead += slice.size();
      } else {
        cache_.emplace_back("");
      }

      if (values_) {
        leveldb::Slice slice = CurrentValue();
        cache_.emplace_back(slice.data(), slice.size());
        bytesRead += slice.size();
      } else {
        cache_.emplace_back("");
      }

      if (!landed_) {
        landed_ = true;
        return true;
      }

      if (bytesRead > highWaterMark_ || cache_.size() >= size * 2) {
        return true;
      }
    }

    return false;
  }

  const uint32_t id_;
  const bool keys_;
  const bool values_;
  const bool keyAsBuffer_;
  const bool valueAsBuffer_;
  const uint32_t highWaterMark_;
  bool landed_;
  bool nexting_;
  bool isEnding_;
  BaseWorker* endWorker_;
  std::vector<std::string> cache_;

private:
  napi_ref ref_;
};

/**
 * Hook for when the environment exits. This hook will be called after
 * already-scheduled napi_async_work items have finished, which gives us
 * the guarantee that no db operations will be in-flight at this time.
 */
static void env_cleanup_hook (void* arg) {
  Database* database = (Database*)arg;

  // Do everything that db_close() does but synchronously. We're expecting that GC
  // did not (yet) collect the database because that would be a user mistake (not
  // closing their db) made during the lifetime of the environment. That's different
  // from an environment being torn down (like the main process or a worker thread)
  // where it's our responsibility to clean up. Note also, the following code must
  // be a safe noop if called before db_open() or after db_close().
  if (database && database->db_ != NULL) {
    std::map<uint32_t, Iterator*> iterators = database->iterators_;
    std::map<uint32_t, Iterator*>::iterator it;

    // TODO: does not do `napi_delete_reference(env, iterator->ref_)`. Problem?
    for (it = iterators.begin(); it != iterators.end(); ++it) {
      it->second->End();
    }

    // Having ended the iterators (and released snapshots) we can safely close.
    database->CloseDatabase();
  }
}

/**
 * Runs when a Database is garbage collected.
 */
static void FinalizeDatabase (napi_env env, void* data, void* hint) {
  if (data) {
    Database* database = (Database*)data;
    napi_remove_env_cleanup_hook(env, env_cleanup_hook, database);
    if (database->ref_ != NULL) napi_delete_reference(env, database->ref_);
    delete database;
  }
}

/**
 * Returns a context object for a database.
 */
NAPI_METHOD(db_init) {
  Database* database = new Database();
  napi_add_env_cleanup_hook(env, env_cleanup_hook, database);

  napi_value result;
  NAPI_STATUS_THROWS(napi_create_external(env, database,
                                          FinalizeDatabase,
                                          NULL, &result));

  // Reference counter to prevent GC of database while priority workers are active
  NAPI_STATUS_THROWS(napi_create_reference(env, result, 0, &database->ref_));

  return result;
}

/**
 * Worker class for opening a database.
 * TODO: shouldn't this be a PriorityWorker?
 */
struct OpenWorker final : public BaseWorker {
  OpenWorker (napi_env env,
              Database* database,
              napi_value callback,
              const std::string& location,
              const bool createIfMissing,
              const bool errorIfExists,
              const bool compression,
              const uint32_t writeBufferSize,
              const uint32_t blockSize,
              const uint32_t maxOpenFiles,
              const uint32_t blockRestartInterval,
              const uint32_t maxFileSize)
    : BaseWorker(env, database, callback, "leveldown.db.open"),
      location_(location) {
    options_.block_cache = database->blockCache_;
    options_.filter_policy = database->filterPolicy_;
    options_.create_if_missing = createIfMissing;
    options_.error_if_exists = errorIfExists;
    options_.compression = compression
      ? leveldb::kSnappyCompression
      : leveldb::kNoCompression;
    options_.write_buffer_size = writeBufferSize;
    options_.block_size = blockSize;
    options_.max_open_files = maxOpenFiles;
    options_.block_restart_interval = blockRestartInterval;
    options_.max_file_size = maxFileSize;
  }

  ~OpenWorker () {}

  void DoExecute () override {
    SetStatus(database_->Open(options_, location_.c_str()));
  }

  leveldb::Options options_;
  std::string location_;
};

/**
 * Open a database.
 */
NAPI_METHOD(db_open) {
  NAPI_ARGV(4);
  NAPI_DB_CONTEXT();
  NAPI_ARGV_UTF8_NEW(location, 1);

  napi_value options = argv[2];
  const bool createIfMissing = BooleanProperty(env, options, "createIfMissing", true);
  const bool errorIfExists = BooleanProperty(env, options, "errorIfExists", false);
  const bool compression = BooleanProperty(env, options, "compression", true);

  const uint32_t cacheSize = Uint32Property(env, options, "cacheSize", 8 << 20);
  const uint32_t writeBufferSize = Uint32Property(env, options , "writeBufferSize" , 4 << 20);
  const uint32_t blockSize = Uint32Property(env, options, "blockSize", 4096);
  const uint32_t maxOpenFiles = Uint32Property(env, options, "maxOpenFiles", 1000);
  const uint32_t blockRestartInterval = Uint32Property(env, options,
                                                 "blockRestartInterval", 16);
  const uint32_t maxFileSize = Uint32Property(env, options, "maxFileSize", 2 << 20);

  database->blockCache_ = leveldb::NewLRUCache(cacheSize);

  napi_value callback = argv[3];
  OpenWorker* worker = new OpenWorker(env, database, callback, location,
                                      createIfMissing, errorIfExists,
                                      compression, writeBufferSize, blockSize,
                                      maxOpenFiles, blockRestartInterval,
                                      maxFileSize);
  worker->Queue(env);
  delete [] location;

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for closing a database
 */
struct CloseWorker final : public BaseWorker {
  CloseWorker (napi_env env,
               Database* database,
               napi_value callback)
    : BaseWorker(env, database, callback, "leveldown.db.close") {}

  ~CloseWorker () {}

  void DoExecute () override {
    database_->CloseDatabase();
  }
};

napi_value noop_callback (napi_env env, napi_callback_info info) {
  return 0;
}

/**
 * Close a database.
 */
NAPI_METHOD(db_close) {
  NAPI_ARGV(2);
  NAPI_DB_CONTEXT();

  napi_value callback = argv[1];
  CloseWorker* worker = new CloseWorker(env, database, callback);

  if (!database->HasPriorityWork()) {
    worker->Queue(env);
    NAPI_RETURN_UNDEFINED();
  }

  database->pendingCloseWorker_ = worker;

  napi_value noop;
  napi_create_function(env, NULL, 0, noop_callback, NULL, &noop);

  std::map<uint32_t, Iterator*> iterators = database->iterators_;
  std::map<uint32_t, Iterator*>::iterator it;

  for (it = iterators.begin(); it != iterators.end(); ++it) {
    iterator_end_do(env, it->second, noop);
  }

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for putting key/value to the database
 */
struct PutWorker final : public PriorityWorker {
  PutWorker (napi_env env,
             Database* database,
             napi_value callback,
             leveldb::Slice key,
             leveldb::Slice value,
             bool sync)
    : PriorityWorker(env, database, callback, "leveldown.db.put"),
      key_(key), value_(value) {
    options_.sync = sync;
  }

  ~PutWorker () {
    DisposeSliceBuffer(key_);
    DisposeSliceBuffer(value_);
  }

  void DoExecute () override {
    SetStatus(database_->Put(options_, key_, value_));
  }

  leveldb::WriteOptions options_;
  leveldb::Slice key_;
  leveldb::Slice value_;
};

/**
 * Puts a key and a value to a database.
 */
NAPI_METHOD(db_put) {
  NAPI_ARGV(5);
  NAPI_DB_CONTEXT();

  leveldb::Slice key = ToSlice(env, argv[1]);
  leveldb::Slice value = ToSlice(env, argv[2]);
  bool sync = BooleanProperty(env, argv[3], "sync", false);
  napi_value callback = argv[4];

  PutWorker* worker = new PutWorker(env, database, callback, key, value, sync);
  worker->Queue(env);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for getting a value from a database.
 */
struct GetWorker final : public PriorityWorker {
  GetWorker (napi_env env,
             Database* database,
             napi_value callback,
             leveldb::Slice key,
             const bool asBuffer,
             const bool fillCache)
    : PriorityWorker(env, database, callback, "leveldown.db.get"),
      key_(key),
      asBuffer_(asBuffer) {
    options_.fill_cache = fillCache;
  }

  ~GetWorker () {
    DisposeSliceBuffer(key_);
  }

  void DoExecute () override {
    SetStatus(database_->Get(options_, key_, value_));
  }

  void HandleOKCallback (napi_env env, napi_value callback) override {
    napi_value argv[2];
    napi_get_null(env, &argv[0]);
    Entry::Convert(env, &value_, asBuffer_, &argv[1]);
    CallFunction(env, callback, 2, argv);
  }

private:
  leveldb::ReadOptions options_;
  leveldb::Slice key_;
  std::string value_;
  const bool asBuffer_;
};

/**
 * Gets a value from a database.
 */
NAPI_METHOD(db_get) {
  NAPI_ARGV(4);
  NAPI_DB_CONTEXT();

  leveldb::Slice key = ToSlice(env, argv[1]);
  napi_value options = argv[2];
  const bool asBuffer = BooleanProperty(env, options, "asBuffer", true);
  const bool fillCache = BooleanProperty(env, options, "fillCache", true);
  napi_value callback = argv[3];

  GetWorker* worker = new GetWorker(env, database, callback, key, asBuffer,
                                    fillCache);
  worker->Queue(env);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for getting many values.
 */
struct GetManyWorker final : public PriorityWorker {
  GetManyWorker (napi_env env,
                 Database* database,
                 const std::vector<std::string>* keys,
                 napi_value callback,
                 const bool valueAsBuffer,
                 const bool fillCache)
    : PriorityWorker(env, database, callback, "leveldown.get.many"),
      keys_(keys), valueAsBuffer_(valueAsBuffer) {
      options_.fill_cache = fillCache;
      options_.snapshot = database->NewSnapshot();
    }

  ~GetManyWorker() {
    delete keys_;
  }

  void DoExecute () override {
    cache_.reserve(keys_->size());

    for (const std::string& key: *keys_) {
      std::string* value = new std::string();
      leveldb::Status status = database_->Get(options_, key, *value);

      if (status.ok()) {
        cache_.push_back(value);
      } else if (status.IsNotFound()) {
        delete value;
        cache_.push_back(NULL);
      } else {
        delete value;
        for (const std::string* value: cache_) {
          if (value != NULL) delete value;
        }
        SetStatus(status);
        break;
      }
    }

    database_->ReleaseSnapshot(options_.snapshot);
  }

  void HandleOKCallback (napi_env env, napi_value callback) override {
    size_t size = cache_.size();
    napi_value array;
    napi_create_array_with_length(env, size, &array);

    for (size_t idx = 0; idx < size; idx++) {
      std::string* value = cache_[idx];
      napi_value element;
      Entry::Convert(env, value, valueAsBuffer_, &element);
      napi_set_element(env, array, static_cast<uint32_t>(idx), element);
      if (value != NULL) delete value;
    }

    napi_value argv[2];
    napi_get_null(env, &argv[0]);
    argv[1] = array;
    CallFunction(env, callback, 2, argv);
  }

private:
  leveldb::ReadOptions options_;
  const std::vector<std::string>* keys_;
  const bool valueAsBuffer_;
  std::vector<std::string*> cache_;
};

/**
 * Gets many values from a database.
 */
NAPI_METHOD(db_get_many) {
  NAPI_ARGV(4);
  NAPI_DB_CONTEXT();

  const std::vector<std::string>* keys = KeyArray(env, argv[1]);
  napi_value options = argv[2];
  const bool asBuffer = BooleanProperty(env, options, "asBuffer", true);
  const bool fillCache = BooleanProperty(env, options, "fillCache", true);
  napi_value callback = argv[3];

  GetManyWorker* worker = new GetManyWorker(
    env, database, keys, callback, asBuffer, fillCache
  );

  worker->Queue(env);
  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for deleting a value from a database.
 */
struct DelWorker final : public PriorityWorker {
  DelWorker (napi_env env,
             Database* database,
             napi_value callback,
             leveldb::Slice key,
             bool sync)
    : PriorityWorker(env, database, callback, "leveldown.db.del"),
      key_(key) {
    options_.sync = sync;
  }

  ~DelWorker () {
    DisposeSliceBuffer(key_);
  }

  void DoExecute () override {
    SetStatus(database_->Del(options_, key_));
  }

  leveldb::WriteOptions options_;
  leveldb::Slice key_;
};

/**
 * Delete a value from a database.
 */
NAPI_METHOD(db_del) {
  NAPI_ARGV(4);
  NAPI_DB_CONTEXT();

  leveldb::Slice key = ToSlice(env, argv[1]);
  bool sync = BooleanProperty(env, argv[2], "sync", false);
  napi_value callback = argv[3];

  DelWorker* worker = new DelWorker(env, database, callback, key, sync);
  worker->Queue(env);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for deleting a range from a database.
 */
struct ClearWorker final : public PriorityWorker {
  ClearWorker (napi_env env,
               Database* database,
               napi_value callback,
               const bool reverse,
               const int limit,
               std::string* lt,
               std::string* lte,
               std::string* gt,
               std::string* gte)
    : PriorityWorker(env, database, callback, "leveldown.db.clear") {
    iterator_ = new BaseIterator(database, reverse, lt, lte, gt, gte, limit, false);
    writeOptions_ = new leveldb::WriteOptions();
    writeOptions_->sync = false;
  }

  ~ClearWorker () {
    delete iterator_;
    delete writeOptions_;
  }

  void DoExecute () override {
    iterator_->SeekToRange();

    // TODO: add option
    uint32_t hwm = 16 * 1024;
    leveldb::WriteBatch batch;

    while (true) {
      size_t bytesRead = 0;

      while (bytesRead <= hwm && iterator_->Valid() && iterator_->Increment()) {
        leveldb::Slice key = iterator_->CurrentKey();
        batch.Delete(key);
        bytesRead += key.size();
        iterator_->Next();
      }

      if (!SetStatus(iterator_->Status()) || bytesRead == 0) {
        break;
      }

      if (!SetStatus(database_->WriteBatch(*writeOptions_, &batch))) {
        break;
      }

      batch.Clear();
    }

    iterator_->End();
  }

private:
  BaseIterator* iterator_;
  leveldb::WriteOptions* writeOptions_;
};

/**
 * Delete a range from a database.
 */
NAPI_METHOD(db_clear) {
  NAPI_ARGV(3);
  NAPI_DB_CONTEXT();

  napi_value options = argv[1];
  napi_value callback = argv[2];

  const bool reverse = BooleanProperty(env, options, "reverse", false);
  const int limit = Int32Property(env, options, "limit", -1);

  std::string* lt = RangeOption(env, options, "lt");
  std::string* lte = RangeOption(env, options, "lte");
  std::string* gt = RangeOption(env, options, "gt");
  std::string* gte = RangeOption(env, options, "gte");

  ClearWorker* worker = new ClearWorker(env, database, callback, reverse, limit, lt, lte, gt, gte);
  worker->Queue(env);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for calculating the size of a range.
 */
struct ApproximateSizeWorker final : public PriorityWorker {
  ApproximateSizeWorker (napi_env env,
                         Database* database,
                         napi_value callback,
                         leveldb::Slice start,
                         leveldb::Slice end)
    : PriorityWorker(env, database, callback, "leveldown.db.approximate_size"),
      start_(start), end_(end) {}

  ~ApproximateSizeWorker () {
    DisposeSliceBuffer(start_);
    DisposeSliceBuffer(end_);
  }

  void DoExecute () override {
    leveldb::Range range(start_, end_);
    size_ = database_->ApproximateSize(&range);
  }

  void HandleOKCallback (napi_env env, napi_value callback) override {
    napi_value argv[2];
    napi_get_null(env, &argv[0]);
    napi_create_int64(env, (uint64_t)size_, &argv[1]);
    CallFunction(env, callback, 2, argv);
  }

  leveldb::Slice start_;
  leveldb::Slice end_;
  uint64_t size_;
};

/**
 * Calculates the approximate size of a range in a database.
 */
NAPI_METHOD(db_approximate_size) {
  NAPI_ARGV(4);
  NAPI_DB_CONTEXT();

  leveldb::Slice start = ToSlice(env, argv[1]);
  leveldb::Slice end = ToSlice(env, argv[2]);

  napi_value callback = argv[3];

  ApproximateSizeWorker* worker  = new ApproximateSizeWorker(env, database,
                                                             callback, start,
                                                             end);
  worker->Queue(env);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for compacting a range in a database.
 */
struct CompactRangeWorker final : public PriorityWorker {
  CompactRangeWorker (napi_env env,
                      Database* database,
                      napi_value callback,
                      leveldb::Slice start,
                      leveldb::Slice end)
    : PriorityWorker(env, database, callback, "leveldown.db.compact_range"),
      start_(start), end_(end) {}

  ~CompactRangeWorker () {
    DisposeSliceBuffer(start_);
    DisposeSliceBuffer(end_);
  }

  void DoExecute () override {
    database_->CompactRange(&start_, &end_);
  }

  leveldb::Slice start_;
  leveldb::Slice end_;
};

/**
 * Compacts a range in a database.
 */
NAPI_METHOD(db_compact_range) {
  NAPI_ARGV(4);
  NAPI_DB_CONTEXT();

  leveldb::Slice start = ToSlice(env, argv[1]);
  leveldb::Slice end = ToSlice(env, argv[2]);
  napi_value callback = argv[3];

  CompactRangeWorker* worker  = new CompactRangeWorker(env, database, callback,
                                                       start, end);
  worker->Queue(env);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Get a property from a database.
 */
NAPI_METHOD(db_get_property) {
  NAPI_ARGV(2);
  NAPI_DB_CONTEXT();

  leveldb::Slice property = ToSlice(env, argv[1]);

  std::string value;
  database->GetProperty(property, &value);

  napi_value result;
  napi_create_string_utf8(env, value.data(), value.size(), &result);

  DisposeSliceBuffer(property);

  return result;
}

/**
 * Worker class for destroying a database.
 */
struct DestroyWorker final : public BaseWorker {
  DestroyWorker (napi_env env,
                 const std::string& location,
                 napi_value callback)
    : BaseWorker(env, NULL, callback, "leveldown.destroy_db"),
      location_(location) {}

  ~DestroyWorker () {}

  void DoExecute () override {
    leveldb::Options options;
    SetStatus(leveldb::DestroyDB(location_, options));
  }

  std::string location_;
};

/**
 * Destroys a database.
 */
NAPI_METHOD(destroy_db) {
  NAPI_ARGV(2);
  NAPI_ARGV_UTF8_NEW(location, 0);
  napi_value callback = argv[1];

  DestroyWorker* worker = new DestroyWorker(env, location, callback);
  worker->Queue(env);

  delete [] location;

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for repairing a database.
 */
struct RepairWorker final : public BaseWorker {
  RepairWorker (napi_env env,
                const std::string& location,
                napi_value callback)
    : BaseWorker(env, NULL, callback, "leveldown.repair_db"),
      location_(location) {}

  ~RepairWorker () {}

  void DoExecute () override {
    leveldb::Options options;
    SetStatus(leveldb::RepairDB(location_, options));
  }

  std::string location_;
};

/**
 * Repairs a database.
 */
NAPI_METHOD(repair_db) {
  NAPI_ARGV(2);
  NAPI_ARGV_UTF8_NEW(location, 0);
  napi_value callback = argv[1];

  RepairWorker* worker = new RepairWorker(env, location, callback);
  worker->Queue(env);

  delete [] location;

  NAPI_RETURN_UNDEFINED();
}

/**
 * Runs when an Iterator is garbage collected.
 */
static void FinalizeIterator (napi_env env, void* data, void* hint) {
  if (data) {
    delete (Iterator*)data;
  }
}

/**
 * Create an iterator.
 */
NAPI_METHOD(iterator_init) {
  NAPI_ARGV(2);
  NAPI_DB_CONTEXT();

  napi_value options = argv[1];
  const bool reverse = BooleanProperty(env, options, "reverse", false);
  const bool keys = BooleanProperty(env, options, "keys", true);
  const bool values = BooleanProperty(env, options, "values", true);
  const bool fillCache = BooleanProperty(env, options, "fillCache", false);
  const bool keyAsBuffer = BooleanProperty(env, options, "keyAsBuffer", true);
  const bool valueAsBuffer = BooleanProperty(env, options, "valueAsBuffer", true);
  const int limit = Int32Property(env, options, "limit", -1);
  const uint32_t highWaterMark = Uint32Property(env, options, "highWaterMark",
                                          16 * 1024);

  std::string* lt = RangeOption(env, options, "lt");
  std::string* lte = RangeOption(env, options, "lte");
  std::string* gt = RangeOption(env, options, "gt");
  std::string* gte = RangeOption(env, options, "gte");

  const uint32_t id = database->currentIteratorId_++;
  Iterator* iterator = new Iterator(database, id, reverse, keys,
                                    values, limit, lt, lte, gt, gte, fillCache,
                                    keyAsBuffer, valueAsBuffer, highWaterMark);
  napi_value result;

  NAPI_STATUS_THROWS(napi_create_external(env, iterator,
                                          FinalizeIterator,
                                          NULL, &result));

  // Prevent GC of JS object before the iterator is ended (explicitly or on
  // db close) and keep track of non-ended iterators to end them on db close.
  iterator->Attach(env, result);

  return result;
}

/**
 * Seeks an iterator.
 */
NAPI_METHOD(iterator_seek) {
  NAPI_ARGV(2);
  NAPI_ITERATOR_CONTEXT();

  if (iterator->isEnding_ || iterator->hasEnded_) {
    napi_throw_error(env, NULL, "iterator has ended");
  }

  leveldb::Slice target = ToSlice(env, argv[1]);
  iterator->landed_ = false;
  iterator->Seek(target);

  DisposeSliceBuffer(target);
  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for ending an iterator
 */
struct EndWorker final : public BaseWorker {
  EndWorker (napi_env env,
             Iterator* iterator,
             napi_value callback)
    : BaseWorker(env, iterator->database_, callback, "leveldown.iterator.end"),
      iterator_(iterator) {}

  ~EndWorker () {}

  void DoExecute () override {
    iterator_->End();
  }

  void DoFinally (napi_env env) override {
    iterator_->Detach(env);
    BaseWorker::DoFinally(env);
  }

private:
  Iterator* iterator_;
};

/**
 * Called by NAPI_METHOD(iterator_end) and also when closing
 * open iterators during NAPI_METHOD(db_close).
 */
static void iterator_end_do (napi_env env, Iterator* iterator, napi_value cb) {
  if (!iterator->isEnding_ && !iterator->hasEnded_) {
    EndWorker* worker = new EndWorker(env, iterator, cb);
    iterator->isEnding_ = true;

    if (iterator->nexting_) {
      iterator->endWorker_ = worker;
    } else {
      worker->Queue(env);
    }
  }
}

/**
 * Ends an iterator.
 */
NAPI_METHOD(iterator_end) {
  NAPI_ARGV(2);
  NAPI_ITERATOR_CONTEXT();

  iterator_end_do(env, iterator, argv[1]);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for nexting an iterator.
 */
struct NextWorker final : public BaseWorker {
  NextWorker (napi_env env,
              Iterator* iterator,
              napi_value callback)
    : BaseWorker(env, iterator->database_, callback,
                 "leveldown.iterator.next"),
      iterator_(iterator), ok_() {}

  ~NextWorker () {}

  void DoExecute () override {
    if (!iterator_->DidSeek()) {
      iterator_->SeekToRange();
    }

    // Limit the size of the cache to prevent starving the event loop
    // in JS-land while we're recursively calling process.nextTick().
    ok_ = iterator_->ReadMany(1000);

    if (!ok_) {
      SetStatus(iterator_->Status());
    }
  }

  void HandleOKCallback (napi_env env, napi_value callback) override {
    size_t arraySize = iterator_->cache_.size();
    napi_value jsArray;
    napi_create_array_with_length(env, arraySize, &jsArray);

    for (size_t idx = 0; idx < iterator_->cache_.size(); idx += 2) {
      std::string key = iterator_->cache_[idx];
      std::string value = iterator_->cache_[idx + 1];

      napi_value returnKey;
      napi_value returnValue;

      Entry::Convert(env, &key, iterator_->keyAsBuffer_, &returnKey);
      Entry::Convert(env, &value, iterator_->valueAsBuffer_, &returnValue);

      // put the key & value in a descending order, so that they can be .pop:ed in javascript-land
      napi_set_element(env, jsArray, static_cast<int>(arraySize - idx - 1), returnKey);
      napi_set_element(env, jsArray, static_cast<int>(arraySize - idx - 2), returnValue);
    }

    napi_value argv[3];
    napi_get_null(env, &argv[0]);
    argv[1] = jsArray;
    napi_get_boolean(env, !ok_, &argv[2]);
    CallFunction(env, callback, 3, argv);
  }

  void DoFinally (napi_env env) override {
    // clean up & handle the next/end state
    iterator_->nexting_ = false;

    if (iterator_->endWorker_ != NULL) {
      iterator_->endWorker_->Queue(env);
      iterator_->endWorker_ = NULL;
    }

    BaseWorker::DoFinally(env);
  }

private:
  Iterator* iterator_;
  bool ok_;
};

/**
 * Moves an iterator to next element.
 */
NAPI_METHOD(iterator_next) {
  NAPI_ARGV(2);
  NAPI_ITERATOR_CONTEXT();

  napi_value callback = argv[1];

  if (iterator->isEnding_ || iterator->hasEnded_) {
    napi_value argv = CreateError(env, "iterator has ended");
    CallFunction(env, callback, 1, &argv);

    NAPI_RETURN_UNDEFINED();
  }

  NextWorker* worker = new NextWorker(env, iterator, callback);
  iterator->nexting_ = true;
  worker->Queue(env);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for batch write operation.
 */
struct BatchWorker final : public PriorityWorker {
  BatchWorker (napi_env env,
               Database* database,
               napi_value callback,
               leveldb::WriteBatch* batch,
               const bool sync,
               const bool hasData)
    : PriorityWorker(env, database, callback, "leveldown.batch.do"),
      batch_(batch), hasData_(hasData) {
    options_.sync = sync;
  }

  ~BatchWorker () {
    delete batch_;
  }

  void DoExecute () override {
    if (hasData_) {
      SetStatus(database_->WriteBatch(options_, batch_));
    }
  }

private:
  leveldb::WriteOptions options_;
  leveldb::WriteBatch* batch_;
  const bool hasData_;
};

/**
 * Does a batch write operation on a database.
 */
NAPI_METHOD(batch_do) {
  NAPI_ARGV(4);
  NAPI_DB_CONTEXT();

  napi_value array = argv[1];
  const bool sync = BooleanProperty(env, argv[2], "sync", false);
  napi_value callback = argv[3];

  uint32_t length;
  napi_get_array_length(env, array, &length);

  leveldb::WriteBatch* batch = new leveldb::WriteBatch();
  bool hasData = false;

  for (uint32_t i = 0; i < length; i++) {
    napi_value element;
    napi_get_element(env, array, i, &element);

    if (!IsObject(env, element)) continue;

    std::string type = StringProperty(env, element, "type");

    if (type == "del") {
      if (!HasProperty(env, element, "key")) continue;
      leveldb::Slice key = ToSlice(env, GetProperty(env, element, "key"));

      batch->Delete(key);
      if (!hasData) hasData = true;

      DisposeSliceBuffer(key);
    } else if (type == "put") {
      if (!HasProperty(env, element, "key")) continue;
      if (!HasProperty(env, element, "value")) continue;

      leveldb::Slice key = ToSlice(env, GetProperty(env, element, "key"));
      leveldb::Slice value = ToSlice(env, GetProperty(env, element, "value"));

      batch->Put(key, value);
      if (!hasData) hasData = true;

      DisposeSliceBuffer(key);
      DisposeSliceBuffer(value);
    }
  }

  BatchWorker* worker = new BatchWorker(env, database, callback, batch, sync, hasData);
  worker->Queue(env);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Owns a WriteBatch.
 */
struct Batch {
  Batch (Database* database)
    : database_(database),
      batch_(new leveldb::WriteBatch()),
      hasData_(false) {}

  ~Batch () {
    delete batch_;
  }

  void Put (leveldb::Slice key, leveldb::Slice value) {
    batch_->Put(key, value);
    hasData_ = true;
  }

  void Del (leveldb::Slice key) {
    batch_->Delete(key);
    hasData_ = true;
  }

  void Clear () {
    batch_->Clear();
    hasData_ = false;
  }

  leveldb::Status Write (bool sync) {
    leveldb::WriteOptions options;
    options.sync = sync;
    return database_->WriteBatch(options, batch_);
  }

  Database* database_;
  leveldb::WriteBatch* batch_;
  bool hasData_;
};

/**
 * Runs when a Batch is garbage collected.
 */
static void FinalizeBatch (napi_env env, void* data, void* hint) {
  if (data) {
    delete (Batch*)data;
  }
}

/**
 * Return a batch object.
 */
NAPI_METHOD(batch_init) {
  NAPI_ARGV(1);
  NAPI_DB_CONTEXT();

  Batch* batch = new Batch(database);

  napi_value result;
  NAPI_STATUS_THROWS(napi_create_external(env, batch,
                                          FinalizeBatch,
                                          NULL, &result));
  return result;
}

/**
 * Adds a put instruction to a batch object.
 */
NAPI_METHOD(batch_put) {
  NAPI_ARGV(3);
  NAPI_BATCH_CONTEXT();

  leveldb::Slice key = ToSlice(env, argv[1]);
  leveldb::Slice value = ToSlice(env, argv[2]);
  batch->Put(key, value);
  DisposeSliceBuffer(key);
  DisposeSliceBuffer(value);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Adds a delete instruction to a batch object.
 */
NAPI_METHOD(batch_del) {
  NAPI_ARGV(2);
  NAPI_BATCH_CONTEXT();

  leveldb::Slice key = ToSlice(env, argv[1]);
  batch->Del(key);
  DisposeSliceBuffer(key);

  NAPI_RETURN_UNDEFINED();
}

/**
 * Clears a batch object.
 */
NAPI_METHOD(batch_clear) {
  NAPI_ARGV(1);
  NAPI_BATCH_CONTEXT();

  batch->Clear();

  NAPI_RETURN_UNDEFINED();
}

/**
 * Worker class for batch write operation.
 */
struct BatchWriteWorker final : public PriorityWorker {
  BatchWriteWorker (napi_env env,
                    napi_value context,
                    Batch* batch,
                    napi_value callback,
                    const bool sync)
    : PriorityWorker(env, batch->database_, callback, "leveldown.batch.write"),
      batch_(batch),
      sync_(sync) {
        // Prevent GC of batch object before we execute
        NAPI_STATUS_THROWS_VOID(napi_create_reference(env, context, 1, &contextRef_));
      }

  ~BatchWriteWorker () {}

  void DoExecute () override {
    if (batch_->hasData_) {
      SetStatus(batch_->Write(sync_));
    }
  }

  void DoFinally (napi_env env) override {
    napi_delete_reference(env, contextRef_);
    PriorityWorker::DoFinally(env);
  }

private:
  Batch* batch_;
  const bool sync_;
  napi_ref contextRef_;
};

/**
 * Writes a batch object.
 */
NAPI_METHOD(batch_write) {
  NAPI_ARGV(3);
  NAPI_BATCH_CONTEXT();

  napi_value options = argv[1];
  const bool sync = BooleanProperty(env, options, "sync", false);
  napi_value callback = argv[2];

  BatchWriteWorker* worker  = new BatchWriteWorker(env, argv[0], batch, callback, sync);
  worker->Queue(env);

  NAPI_RETURN_UNDEFINED();
}

/**
 * All exported functions.
 */
NAPI_INIT() {
  NAPI_EXPORT_FUNCTION(db_init);
  NAPI_EXPORT_FUNCTION(db_open);
  NAPI_EXPORT_FUNCTION(db_close);
  NAPI_EXPORT_FUNCTION(db_put);
  NAPI_EXPORT_FUNCTION(db_get);
  NAPI_EXPORT_FUNCTION(db_get_many);
  NAPI_EXPORT_FUNCTION(db_del);
  NAPI_EXPORT_FUNCTION(db_clear);
  NAPI_EXPORT_FUNCTION(db_approximate_size);
  NAPI_EXPORT_FUNCTION(db_compact_range);
  NAPI_EXPORT_FUNCTION(db_get_property);

  NAPI_EXPORT_FUNCTION(destroy_db);
  NAPI_EXPORT_FUNCTION(repair_db);

  NAPI_EXPORT_FUNCTION(iterator_init);
  NAPI_EXPORT_FUNCTION(iterator_seek);
  NAPI_EXPORT_FUNCTION(iterator_end);
  NAPI_EXPORT_FUNCTION(iterator_next);

  NAPI_EXPORT_FUNCTION(batch_do);
  NAPI_EXPORT_FUNCTION(batch_init);
  NAPI_EXPORT_FUNCTION(batch_put);
  NAPI_EXPORT_FUNCTION(batch_del);
  NAPI_EXPORT_FUNCTION(batch_clear);
  NAPI_EXPORT_FUNCTION(batch_write);
}
