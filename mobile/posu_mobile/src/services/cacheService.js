
const CACHE_KEYS = {
  VIOLATION_TYPES: "cached_violation_types",
  VIOLATORS: "cached_violators",
  CACHE_TIMESTAMP: "cache_timestamp"
};

const CACHE_DURATION = 24 * 60 * 60 * 1000; 

const isCacheValid = (timestamp) => {
  if (!timestamp) return false;
  return (Date.now() - timestamp) < CACHE_DURATION;
};

const readCache = (key) => {
  try {
    const data = localStorage.getItem(key);
    return data ? JSON.parse(data) : null;
  } catch (e) {
    console.error(`Error reading cache for ${key}:`, e);
    return null;
  }
};

const writeCache = (key, data) => {
  try {
    localStorage.setItem(key, JSON.stringify(data));
    localStorage.setItem(CACHE_KEYS.CACHE_TIMESTAMP, Date.now().toString());
  } catch (e) {
    console.error(`Error writing cache for ${key}:`, e);
  }
};

export const cacheService = {
  // Violation Types Cache
  getViolationTypes() {
    const cached = readCache(CACHE_KEYS.VIOLATION_TYPES);
    const timestamp = readCache(CACHE_KEYS.CACHE_TIMESTAMP);
    
    if (cached && isCacheValid(timestamp)) {
      return cached;
    }
    return null;
  },

  setViolationTypes(types) {
    writeCache(CACHE_KEYS.VIOLATION_TYPES, types);
  },

  // Violators Cache
  getViolators() {
    const cached = readCache(CACHE_KEYS.VIOLATORS);
    const timestamp = readCache(CACHE_KEYS.CACHE_TIMESTAMP);
    
    if (cached && isCacheValid(timestamp)) {
      return cached;
    }
    return null;
  },

  setViolators(violators) {
    writeCache(CACHE_KEYS.VIOLATORS, violators);
  },

  // Add new violator to cache (when recording new violation)
  addViolator(violator) {
    const cached = this.getViolators();
    if (cached) {
      // Check if violator already exists (by license number)
      const exists = cached.find(v => v.license_number === violator.license_number);
      if (!exists) {
        cached.push(violator);
        this.setViolators(cached);
      }
    }
  },

  // Search violators locally
  searchViolators(query) {
    const cached = this.getViolators();
    if (!cached || !query) return [];

    const searchTerm = query.toLowerCase();
    const results = cached.filter(violator => {
      const fullName = `${violator.first_name} ${violator.last_name}`.toLowerCase();
      const licenseNumber = violator.license_number?.toLowerCase() || '';
      const mobileNumber = violator.mobile_number?.toLowerCase() || '';
      
      return fullName.includes(searchTerm) || 
             licenseNumber.includes(searchTerm) || 
             mobileNumber.includes(searchTerm);
    });
    
    console.log('Local search for:', query, 'found:', results.length, 'results');
    return results;
  },

  // Clear all cache
  clearCache() {
    Object.values(CACHE_KEYS).forEach(key => {
      localStorage.removeItem(key);
    });
  },

  // Check if cache is valid
  isCacheValid() {
    const timestamp = readCache(CACHE_KEYS.CACHE_TIMESTAMP);
    return isCacheValid(timestamp);
  },

  // Get cache info
  getCacheInfo() {
    const timestamp = readCache(CACHE_KEYS.CACHE_TIMESTAMP);
    const violationTypes = readCache(CACHE_KEYS.VIOLATION_TYPES);
    const violators = readCache(CACHE_KEYS.VIOLATORS);
    
    return {
      isValid: isCacheValid(timestamp),
      lastUpdated: timestamp ? new Date(timestamp) : null,
      violationTypesCount: violationTypes?.length || 0,
      violatorsCount: violators?.length || 0
    };
  },

  // Add sample data for testing (remove this in production)
  addSampleData() {
    const sampleViolators = [
      {
        id: 1,
        first_name: "John",
        last_name: "Doe",
        license_number: "A123456789",
        mobile_number: "09123456789",
        email: "john.doe@email.com",
        barangay: "Sample Barangay",
        city: "Sample City",
        province: "Sample Province"
      },
      {
        id: 2,
        first_name: "Jane",
        last_name: "Smith",
        license_number: "B987654321",
        mobile_number: "09987654321",
        email: "jane.smith@email.com",
        barangay: "Test Barangay",
        city: "Test City",
        province: "Test Province"
      }
    ];
    
    this.setViolators(sampleViolators);
    console.log('Added sample violator data for testing');
  }
};
