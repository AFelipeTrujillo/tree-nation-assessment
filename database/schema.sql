CREATE TABLE IF NOT EXISTS visits (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id TEXT UNIQUE,
    customer_id TEXT NOT NULL,
    shop_id TEXT NOT NULL,
    occurred_at TEXT NOT NULL,
    received_at TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_visits_customer_id
ON visits (customer_id);

CREATE INDEX IF NOT EXISTS idx_visits_occurred_at
ON visits (occurred_at);

CREATE TABLE IF NOT EXISTS customer_stats (
    customer_id TEXT PRIMARY KEY,
    total_visits INTEGER NOT NULL DEFAULT 0,
    trees_planted INTEGER NOT NULL DEFAULT 0,
    last_connection_at TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
