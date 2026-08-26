# Architecture Decision Record (ADR)

## 0012-separate-production-and-dispatch-accounting

**Status**: Accepted

**Context**: 
The facility now tracks finished goods dispatch via Scanner 2. A fundamental architectural question arose: Should dispatching a product reduce the "Total Produced" count to reflect what is left in the factory, or should it be tracked independently?

**Decision**: 
1. **Total Produced** is immutable historical output. It strictly records cumulative completed production from Scanner 1.
2. **Total Dispatched** is a new, independent cumulative metric derived from Scanner 2.
3. **Available Stock** is dynamically calculated as `Total Produced - Total Dispatched`.

**Consequences**:
- `Total Produced` never decreases, preserving the true historical output of the facility.
- Production shortfalls (Current Balance = Requested - Produced) are completely isolated from dispatch operations. A dispatch does not magically recreate a production shortage.
- The system enforces a hard constraint: `Available Stock >= Dispatch Quantity`. Negative inventory is technically impossible, aligning with physical reality.
